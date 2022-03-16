<?php
$connect = mysqli_connect(_HOST_, _USERNAME_, _PASSWORD_);
mysqli_select_db($connect, _DB_);


/*
*  가장 오래된 마지막 로그인 시간을 가져옴
*  마지막 로그인 시간이 현재로부터 1년 이전일 경우 장기간 미접속으로 판단한다고 가정
 */
$unconnectedDate = date("Y-m-d", strtotime("-1 year"));

$query = "SELECT * FROM member WHERE last_login_time < '{$unconnectedDate}' and last_login_time != '0000-00-00 00:00:00' order by last_login_time LIMIT 1";
$result = mysqli_query($connect, $query);
$list = mysqli_fetch_assoc($result);
$startDate = $list['last_login_time'];


/*
 *  slow query 방지를 위해 1회의 장기간의 쿼리를 여러 회의 단기간의 쿼리로 분할
 *  가장 오래된 마지막 로그인 시간으로부터 한달 간격으로 데이터 이전 작업을 반복
 */
while(strtotime($startDate) < strtotime($unconnectedDate)) {
    $endDate = date("Y-m-d", strtotime("+1 month", strtotime($startDate)));

    if(strtotime($endDate) >= strtotime($unconnectedDate)) {
        $endDate = $unconnectedDate;
    }

    try {
        $querySelect = "select * from member where last_login_time >= '{$startDate}' and last_login_time < '{$endDate}'";
        $rs = mysqli_query($connect, $querySelect);

        while($l = mysqli_fetch_assoc($rs)) {
            $queryInsert = "insert into unconnected_member select * from member where idx = {$l['idx']}";
            mysqli_query($connect, $queryInsert);

            if(mysqli_insert_id($connect) > 0) {
                $queryDelete = "delete from member where idx = {$l['idx']}";
                mysqli_query($connect, $queryDelete);
            }
        }

    } catch (Exception $e) {
        echo $startDate . '~' . $endDate . ': ' . $e->getMessage() . '(' . $e->getCode() . ')';

    }

    $startDate = $endDate;
}