#!/bin/bash
inotifywait -q --format '%f' -m -r -e close_write,moved_to,create . | xargs -I{} -r sh -c 'curl https://localhost/api/unpack/$(basename {}) -k'
