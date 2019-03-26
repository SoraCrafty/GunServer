<?php
namespace gun\job;


abstract class Job {

	const JOB_ID = '';
	const JOB_DISCRIPTION = '';
	const JOB_NAME = '';
	
	const EVENT_INTERACT = "onInteract";
	const EVENT_SNEAK = "onSneak";
	const EVENT_MOVE = "onMove";
	const EVENT_DEATH = "onDeath";
	const EVENT_KILL = "onKill";

	protected $plugin;
	
	public function __construct($plugin){
		$this->plugin = $plugin;
	}
	
	public function getId(){
		return static::JOB_ID;
	}
	
	public function getDescription(){
		return static::JOB_DISCRIPTION;
	}
	
	public function getName(){
		return static::JOB_NAME;
	}
	
	public function setup($player){
	}
	
	public function onInteract($player, $event){
	}
	
	public function onSneak($player, $event){
	}
	
	public function onMove($player, $event){
	}
	
	public function onDeath($player, $event){
	}
	
	public function onKill($player, $event){
	}
}
	
	
	
