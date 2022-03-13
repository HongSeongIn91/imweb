# imweb
아임웹 코딩테스트

1. Batch Service로 타 서비스(A)와 운영중인 서비스(B)간 API통신을 통해 데이터를 송수신하고 있다. 이때 통신두절 및 오류발생 등 예상치 못한 문제가 발생하여 데이터 송수신이 정상적으로 이뤄지지 않은경우 어떻게 처리할지 또는 서비스 장애로 이어지지 않게 하기 위한 설계 방안을 기술하시오

 (주)핌즈 재직 당시 고객사가 입점한 다수의 판매처로부터 주문정보를 수집하여 주문상태를 연동하는 이지오토라는 응용프로그램을 전담했을 때의 경험을 말씀드리겠습니다.
 이지오토는 카페24, 고도몰, 쿠팡 등(A)의 타 서비스와 외부API를 통해 데이터를 송수신합니다. API를 이용한 배송중처리 작업에서 데이터 송수신이 정상적으로 이뤄지지 않을 경우 타 서비스 에 주문의 송장 및 택배사 정보가 정상적으로 전송되지 않아 타 서비스 내의 주문상태가 배송중 이전 상태로 남아있게 되고 이는 배송지연으로 인한 주문취소의 가능성을 높입니다. API통신에서 발생하는 서비스 장애는 잘못된 요청을 반복함으로 인해 타 서비스의 API에서 운영 중인 서비스의 해당 요청을 일정시간 동안 제한하여 송수신 자체가 불가능해지는 경우가 있습니다. 
 이는 예상치 못한 문제로 발생하는 송수신 상의 단기적인 문제를 어느 수준에서부터 이지오토 내에서 문제로 인식하여 적절한 조치를 통해 장기적인 장애 상태로 진행되지 않도록 배송중처리 작업을 설계할 것인가에 대한 문제입니다.
 
1. 배송중 처리를 위한 별도의 테이블이 생성되고 API작업대상 주문들이 insert 됩니다.
2. 각 주문에 대해 API요청을 송수신하고 각 주문의 실패 횟수를 테이블에 기록합니다.
3. 다음 배송중 처리 작업이 시작될 때 실패 횟수가 3회 이상인 주문은 작업대상에 예외로 처리하여 포함시키지 않습니다.
4. 실패 횟수가 3회 이상인 주문은 View단을 통해 사용자에게 별도로 노출됩니다.

 이지오토는 송수신 상의 단기적인 문제를 실패 횟수 3회에서부터 문제로 인식하여 별도의 예외처리를 진행합니다. 이외에 가능한 가능한 적절한 조치는 최근 실패 횟수 업데이트 시각을 확인하고 지정된 예외 처리 시간을 설정한 후 예외 처리 시간을 경과한 주문은 다시 배송중 처리 작업의 대상에 포함되도록 하는 방법이 있습니다.

--

2. Batch Service를 만들려고 할때 1분미만의 반복동작되는 Batch Service를 어떻게 만들수 있을지 작성하시오

--

3. DataBase, Api, Page Load 등에서 동일한 결과가 표시되는 데이터들이 존재할때, 어떤기술들을 이용하여 해당 데이터들을 최적화 할 수 있을지 작성하시오

Redis 를 사용해 데이터에 대한 액세스를 최적화할 수 있습니다. Redis는 인메모리 데이터베이스로서 데이터를 디스크가 아닌 메모리에 저장하고 조회할 수 있습니다. 동일한 결과를 표시하는 데이터에 접근하기 위해 Api, Page Load 가 매번 DataBase 에 접근한다면 디스크에 직접 접근해야하기 때문에 부하로 작용할 뿐더러 작업시간 또한 오래 걸립니다. Redis는 기본적으로는 Key-Value 저장 방식을 지원하며, Hash, Lists 등 다양한 자료구조도 지원하므로 동일한 결과를 표시할 지라도 그에 맞는 자료구조를 사용해 개발의 편의성을 높일 수 있습니다.

--

4. 대량의 공격성 접속문제로 서비스가 중단되는 상황이 발생될때, 서비스가 중단되는 원인 확인 및 해결방법에 대해 작성하시오
(서비스 구성은 : linux, nginx, php-fpm, mysql로 구성되어있다고 가정함)

 대량의 공격성 접속은 악의적으로 웹서버, DB의 리소스를 고갈시켜 새로운 트래픽을 생성할 수 없게 하거나 매우 느린 속도로 처리되어 사실상 서비스를 중단되게 만듭니다. 대량의 접속이 감지됐을 경우 nginx, php-fpm 으로 연동된 웹서버의 status, access_log 를 확인하여 연결된 커넥션과 요청, 비정상적인 요청을 시도하는 ip를 확인하여야 합니다. 특정 ip를 차단하는 처리는 대량의 접속 시도를 공격성으로 판단했을 때만 사용하길 권장합니다.
 nginx의 status는 linux 상에서 status nginx 명령어를 통해 확인하거나, url을 사용해 별도의 nginx 상태 정보를 확인할 수 있게 설정했다면 해당 url을 사용해 확인할 수도 있습니다. request 와 connection 값과 access_log 를 확인하여 대량의 공격성 접속이 발생하는 url에 대해 시간당 request 의 비율을 제한하거나 ip당 connection의 수를 제한할 수 있습니다. ip를 특정할 수 있다면 특정 ip에 대해 deny 지시문을 사용해 요청을 거부할 수 있습니다.
 웹서버는 백엔드 서버측으로 연결되기에 mysql 의 status와 processlist 를 확인해야 합니다. 다만 mysql의 status를 확인하여 max_connection 등의 설정을 변경하더라도 shutdown 후 재기동을 해야 변경된 설정이 반영되기에 웹서버 단의 대응보다 제약이 크다고 생각됩니다. processlist 를 확인하여 slow-query나 explain 결과 cost 값이 큰 쿼리를 kill 하여 부하를 감소시킬 수는 있습니다.
 이전 직장에서는 apache, tomcat으로 구성된 웹서버를 운영하였으며 생성된 httpd 프로세스의 개수가 사내에서 설정한 한계치를 넘어갈 경우 restart 를 했었습니다. mysql의 로드 에버리지는 실시간으로 감지되고 있었고 설정한 한계치에 도달할 경우 알람이 발송되며, 개발팀에서 processlist 를 모니터링하여 조치를 취했었습니다.

--

5. member 테이블에서 장기간 미접속한 회원들을 unconnected_member 테이블로 이전시키고자 한다, 가장 호율적으로 이전시킬 수 있는 방법을 코딩하시오
