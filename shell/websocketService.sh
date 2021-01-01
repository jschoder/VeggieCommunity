# This script is supposed to run 24/7!!!
# Don't stop it unless absolutely necessary!!!

php ./run.php websocket RunWebsocketServer server:live > /dev/null 2>&1 & 