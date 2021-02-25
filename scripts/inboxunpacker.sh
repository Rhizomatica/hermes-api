#!/bin/bash

for f in *; do
  echo "File -> $f"
  curl http://localhost/inbox/unpack $f
  # rm $f
done

