#!/bin/bash

# Greig Paul
# 2013-10-02

# This script is used to run the Data Hub server locally on your own computer using Ruby, Sinatra, and Thin. 
# You should already be running a suitable MySQL server before running!

# Syntax: ./startup.sh [start, stop]

if [[ $1 = "start" ]]
    then
    if [ -f .sinatra.pid ]
        then
        echo "Already running, try stop command, or delete .sinatra.pid if you are sure we're not running"
        exit 1
    fi
    ruby local.rb &> /dev/null &
    PID=$!
    # Sets PID variable to the last started PID and stores it
    echo $PID > .sinatra.pid
elif [[ $1 = "stop" ]]
    then
    kill $(cat .sinatra.pid)
    rm .sinatra.pid
else
    echo "Usage: ./startup.sh [start, stop]"
fi