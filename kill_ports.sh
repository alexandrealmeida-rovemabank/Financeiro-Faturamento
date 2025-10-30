#!/bin/bash

for port in {8000..8004}
do
  pid=$(lsof -ti tcp:$port)
  if [ -n "$pid" ]; then
    echo "Matando processo na porta $port (PID: $pid)"
    kill -9 $pid
  else
    echo "Nenhum processo na porta $port"
  fi
done

echo "Todos os processos das portas 8000 a 8004 foram tratados."
