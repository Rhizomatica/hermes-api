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
            return 'for i in $(nncp-stat -pkt | grep dec_sensors | awk \'{print $1}\'); '
                . 'do sudo nncp-rm -all -pkt $i; done';
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
            return 'nncp-stat -node ' . escapeshellarg($dest)
                . " | egrep -o '[0-9]+ B' | awk '{sum+=\$1} END {print sum}'";
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
     * Drop one queued job.
     */
    public static function killJobCommand(string $host, string $id): string
    {
        if (self::isNncp()) {
            return 'sudo nncp-rm -node ' . escapeshellarg($host) . ' -pkt ' . escapeshellarg($id);
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
                ? 'sudo nncp-call -autotoss -all'
                : 'sudo nncp-call -autotoss ' . escapeshellarg($host);
        }

        return $host === null
            ? 'sudo uucico -m -r1 '
            : 'sudo uucico -m -S ' . escapeshellarg($host);
    }

    /**
     * Stop the transfer in progress.
     */
    public static function stopCommand(): string
    {
        return self::isNncp() ? 'sudo killall nncp-call' : 'sudo killall uucico';
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
