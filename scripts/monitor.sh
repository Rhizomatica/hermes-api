#!/bin/bash
inotifywait -q --format '%f' -m -r -e close_write,moved_to . | xargs -I{} -r sh -c 'curl http://localhost/api/unpack/$(basename {}) -k'
