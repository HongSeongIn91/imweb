#!/bin/sh

interval=${1}
divided=`expr 60 / $interval`

if [ $interval -gt 60 ];then
    exit 0
fi

for (( n=1; n<=$divided; n++ ))
do
    echo $n;
    # 배치 작업용 파일을 실행하는 코드
    sleep $interval;
done
exit 0