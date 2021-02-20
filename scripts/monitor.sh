inotifywait -q --format '%f' -m -r -e close_write . | xargs -I{} -r sh -c 'curl http://localhost:8000/unpack/$(basename {})'

