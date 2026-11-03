<?php

namespace App\Services;

/**
 * Store-and-forward transport used over the radio link.
 *
 * HERMES_TRANSPORT selects between the historical plaintext UUCP tools and
 * NNCP, which encrypts and authenticates every packet. The commands below are
 * the only place where the two differ; controllers stay transport agnostic.
 */
class RadioTransport
{
    public static function isNncp(): bool
    {
        return env('HERMES_TRANSPORT', 'uucp') === 'nncp';
    }

    /**
     * Known stations: name and alias.
     */
    public static function stationsCommand(): string
    {
        if (self::isNncp()) {
            // one directory per neighbour, "self" is this station
            return "ls -1 /etc/nncp/neigh 2>/dev/null | grep -v '^self$'";
        }

        return "egrep -v '^\s*#' /etc/uucp/sys | grep system | cut -f 2 -d \" \"";
    }

    /**
     * Station aliases, in the same order as stationsCommand().
     *
     * Under NNCP the neighbour name is already the alias.
     */
    public static function stationAliasesCommand(): string
    {
        if (self::isNncp()) {
            return self::stationsCommand();
        }

        return "egrep -v '^\s*#' /etc/uucp/sys | grep alias | cut -f 2 -d \" \"";
    }

    /**
     * Drop the queued sensor (GPS) jobs.
     */
    public static function purgeSensorJobsCommand(): string
    {
        if (self::isNncp()) {
            // NNCP packets are encrypted for their recipient: the queue shows
            // a packet's id and size, never the file it carries, so sensor
            // jobs cannot be told from the others there. Nothing to purge.
            return 'true';
        }

        return 'for i in $(uustat -a| grep -v uuadm | grep -v sudo | grep -v bash | grep -v "\-C" '
            . '| grep dec_sensors | cut -d " " -f 1); do sudo uustat -k $i; done';
    }

    /**
     * Queue a file for a station.
     */
    public static function sendFileCommand(string $path, string $dest, string $name): string
    {
        if (self::isNncp()) {
            return 'nncp-file -quiet ' . escapeshellarg($path) . ' ' . escapeshellarg($dest . ':' . $name);
        }

        return 'uucp -r -j -C -d ' . escapeshellarg($path) . ' ' . escapeshellarg($dest . '!~/' . $name);
    }

    /**
     * Bytes queued for a station.
     */
    public static function spoolSizeCommand(string $dest): string
    {
        if (self::isNncp()) {
            // the outgoing packets, whose sizes nncp-stat gives rounded, in
            // B, KiB, MiB or GiB: "tx <id> 2.4 MiB (nice: B)"
            return 'nncp-stat -pkt -node ' . escapeshellarg($dest)
                . " | awk '\$1 == \"tx\" { m = \$4 == \"KiB\" ? 1024 : \$4 == \"MiB\" ? 1048576 : "
                . "\$4 == \"GiB\" ? 1073741824 : 1; sum += \$3 * m } END { print int(sum) }'";
        }

        return 'uustat -s ' . escapeshellarg($dest)
            . " -u www-data  | egrep -o '(\w+)\sbytes' | awk -F ' ' '{sum+=\$1; } END {print sum}'";
    }

    /**
     * Queued jobs, one per line.
     */
    public static function queueCommand(): string
    {
        if (self::isNncp()) {
            return 'nncp-stat -pkt';
        }

        return 'uustat -a| grep -v uuadm | grep -v sudo | grep -v bash | grep -v "\-C"';
    }

    /**
     * NNCP's outgoing queue, parsed from nncp-stat -pkt: one entry per
     * packet, its node, id and size in bytes (nncp-stat rounds it).
     *
     * @return array<int, array{node: string, id: string, bytes: int}>
     */
    public static function parseNncpQueue(string $stat): array
    {
        $units = ['B' => 1, 'KiB' => 1024, 'MiB' => 1048576, 'GiB' => 1073741824, 'TiB' => 1099511627776];
        $node = null;
        $packets = [];
        foreach (explode("\n", $stat) as $line) {
            if ($line === '') {
                continue;
            }
            if ($line[0] !== "\t" && $line[0] !== ' ') {
                $node = trim($line);
                continue;
            }
            $f = preg_split('/\s+/', trim($line));
            if ($node !== null && count($f) >= 4 && $f[0] === 'tx' && isset($units[$f[3]])) {
                $packets[] = ['node' => $node, 'id' => $f[1], 'bytes' => (int) round((float) $f[2] * $units[$f[3]])];
            }
        }
        return $packets;
    }

    /**
     * Drop one queued job.
     */
    public static function killJobCommand(string $host, string $id): string
    {
        if (self::isNncp()) {
            // -pkt takes no value: nncp-rm reads the packet names from stdin
            // (an argument after it was ignored, and nothing was removed)
            return 'printf \'%s\\n\' ' . escapeshellarg($id) . ' | sudo nncp-rm -node '
                . escapeshellarg($host) . ' -pkt';
        }

        return 'sudo uustat -k ' . escapeshellarg($host . '.' . $id);
    }

    /**
     * Start a transfer: all stations, or one of them.
     */
    public static function callCommand(?string $host = null): string
    {
        if (self::isNncp()) {
            return $host === null
                ? 'sudo hermes-nncp-call --all -autotoss'
                : 'sudo hermes-nncp-call -autotoss ' . escapeshellarg($host);
        }

        return $host === null
            ? 'sudo uucico -m -r1 ' . self::uucicoAirOpts()
            : 'sudo uucico -m ' . self::uucicoAirOpts($host) . '-S ' . escapeshellarg($host);
    }

    /**
     * uucico options for a call that goes over the air.
     *
     * HERMES_UUCICO_HF_OPTS (the installer sets "-Y", the pre-agreed startup)
     * applies to calls over the radio only. A remote station's systems are
     * all reached over the air. A gateway also reaches the central server,
     * "gw" (system hermes), over TCP, which the pre-agreed startup is not for:
     * there the options go only on a call to a station, never on "-r1", which
     * may call the central server too.
     *
     * @return string the options, with a trailing space, or ""
     */
    private static function uucicoAirOpts(?string $host = null): string
    {
        $opts = trim((string) env('HERMES_UUCICO_HF_OPTS', ''));
        if ($opts === '' || !preg_match('/^-[A-Za-z]+( -[A-Za-z]+)*$/', $opts)) {
            return '';
        }

        if (filter_var(env('HERMES_GATEWAY'), FILTER_VALIDATE_BOOLEAN) &&
            ($host === null || in_array($host, ['gw', 'hermes'], true))) {
            return '';
        }

        return $opts . ' ';
    }

    /**
     * Stop the transfer in progress.
     */
    public static function stopCommand(): string
    {
        return self::isNncp() ? 'sudo killall hermes-nncp-call nncp-call' : 'sudo killall uucico';
    }

    /**
     * Transfer log.
     */
    public static function logCommand(bool $debug = false): string
    {
        if (self::isNncp()) {
            return 'sudo nncp-log | tail -n 1000 | sort -n ';
        }

        return $debug
            ? 'sudo uulog -D -n 1000 | sort -n '
            : 'sudo uulog -n 1000 | sort -n ';
    }

    /**
     * Name of this station.
     */
    public static function nodenameCommand(): string
    {
        if (self::isNncp()) {
            return 'cat /etc/nncp-callsign 2>/dev/null';
        }

        return 'cat /etc/uucp/config|grep nodename|cut -f 2 -d " "';
    }

    /**
     * Process that carries the traffic, for the status page.
     */
    public static function daemonProcess(): string
    {
        return self::isNncp() ? 'nncp-daemon' : 'uucpd';
    }

    /**
     * Unit file of the transport, for the status page.
     */
    public static function serviceUnitCommand(): string
    {
        return self::isNncp()
            ? 'ls /lib/systemd/system/nncp-daemon.service'
            : 'ls  /lib/systemd/system/uucp.socket';
    }
}
