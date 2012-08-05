#php VikingBot.php > logs/vikingbot.log 2>&1 &

while true; do # if bot crashes, restart
  php VikingBot.php | while read line; do
    echo "$line" >> logs/vikingbot.logs
    echo "$line" 
  done  
done
