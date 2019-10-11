<?php

use Helper\Message;
	require '../vendor/autoload.php';
	if (
		(!isset($_POST['from']))||
		(!isset($_POST['to']))||
		(!isset($_POST['time-hour']))||
		(!isset($_POST['time-minute']))||
		(!isset($_POST['date']))||
		(!isset($_POST['company_id']))||
		(!isset($_POST['price']))||
		(!isset($_POST['capacity']))
		) {
		header('location:insert.php');
	}
	require '../vendor/autoload.php';
	use Redis\Redis;
	$redis=Redis::connect();
	
	$travel_data=[
		"to"=>$_POST["to"],
		"from"=>$_POST["from"],
		"date"=>$_POST["date"],
		"time"=>$_POST["time-hour"].':'.$_POST["time-minute"],
		"price"=>$_POST["price"],
		"capacity"=>$_POST["capacity"]
	];
	
	$travelCode=time();
	$date=explode('-', $_POST['date']);
	$fromtodate=$_POST['from'].'-'.$_POST['to'].'-'.$_POST['date'];
	$companyIdYearMonth=$_POST['company_id'].'-'.$date[0].'-'.$date[1];

	$fromToDateTravels=Redis::getArray($redis, $fromtodate);
	$recentTravels=Redis::getArray($redis, 'Recent Travels');
	$companyIdYearMonthTravels=Redis::getArray($redis, $companyIdYearMonth);
	
	if (count($recentTravels)==100) {
		$keys=[];
		foreach ($recentTravels as $key => $value)
			array_push($keys, $key);
		unset($recentTravels[min($keys)]);	
	}

	$recentTravels[$travelCode]=$travel_data;
	$fromToDateTravels[$travelCode]=$travel_data;
	$companyIdYearMonthTravels[$travelCode]=$travel_data;

	$redis->set($travelCode, json_encode($travel_data));
	$redis->set($fromtodate, json_encode($fromToDateTravels));
	$redis->set('Recent Travels', json_encode($recentTravels));
	$redis->set($companyIdYearMonth, json_encode($companyIdYearMonthTravels));
	
	Message::set('travel created');
	header("location:../index.php");
?>