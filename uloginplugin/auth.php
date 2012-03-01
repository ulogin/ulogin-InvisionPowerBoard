<?php

	/**
	* IPBoard plugin uloginplugin
	*
	* @package	Auth
	* @subpackage	uLlogin
	* @author	uLogin
	* @link	http://ulogin.ru
	* @contacts team@ulogin.ru
	*/
	
	if ( ! defined( 'IN_IPB' ) )
	{
		print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
		exit();
	}
	
	class login_uloginplugin extends login_core implements interface_login
	{
		/**
		* Login method configuration
		*
		* @access protected	
		* @var array
		*/
		protected $method_config	= array();
				
		/**
		* Constructor
		*
		* @access public
		* @param ipsRegistry referece
		* @param Configuration info for this method
		* @param Custom configuration infofor this method
		* @return void
		*/
		public function __construct( ipsRegistry $registry, $method, $conf=array() )
		{
			
			$this->method_config	= $method;
			parent::__construct( $registry );
		}
		
		/**
		* Authenticate the request
		*
		* @access public
		* @param string Username
		* @param string Email Address
		* @param string Password
		* @return boolean Authentication successful 
		*/
		public function authenticate( $username, $email_address, $password )
		{
			if(!isset($_POST['token'])) {
				return 'WRONG_AUTH';
			}
			$s = file_get_contents('http://ulogin.ru/token.php?token=' . $_POST['token'] . '&host=' . $_SERVER['HTTP_HOST']);
			$user = json_decode($s, true);
			if(isset($user['error'])) return 'ERROR';
			$ident = $user['identity'];
			$photo = $user['photo'];
			if($photo!='') {
				$ride = "uploads/profile/".md5($ident).".jpg";
				file_put_contents($ride, file_get_contents($photo));
			}
			//Поиск пользователя, которого нужно авторизовать
			$result = $this->DB->buildAndFetch( array(
														'select' => 'id_user,seed',
														'from' => 'ulogin',
														'where' => "ident ='".$user['identity']."'"
													) 
											);
			//На случай, если пользователь удалён из основной базы пользователей, но остался в таблице ulogin
			if($result) {
				$result1 = $this->DB->buildAndFetch( array(
														'select' => 'member_id',
														'from' => 'members',
														'where' => "member_id ='".$result['id_user']."'"
													) 
											);
				if(!$result1) {
					$this->DB->delete('ulogin','id_user='.$result['id_user']);
					$result=false;
				}
				$Passwd=md5($ident.$user['last_name'].$result['seed']);
			}
			
			if(!$result){
				
				//Генерация логина
				$Login21=$user['first_name']."_".$user['last_name'];
				$proof = $this->DB->buildAndFetch( array(
																'select' => 'member_id',
																'from' => 'members',
																'where' => "name='" .$Login21. "'" 
															) 
													 );
				if($proof)
				{
					$Login21=$Login21.$proof['member_id'];
				}
				//Генерация пароля
				$seed=mt_rand();
				$Passwd=md5($ident.$user['last_name'].$seed);
				
				$Email=$user['email'];

				//Добавление пользователя в таблицу members
				$salt= IPSMember::generatePasswordSalt( );
				$hash= md5($salt).md5($Passwd); 
				$this->DB->insert( 'members', array(
														'name'	=> $Login21,
														'member_group_id'			=> 3,
														'email'		=> $Email,
														'members_pass_salt'	=> $salt,
														'members_pass_hash'		=> md5($hash),
														'members_l_username'		=> $Login21,
														'members_display_name'		=> $Login21,
														'members_l_display_name'	=> $Login21,
														
													)
								 );
								
				//Добавление пользователя в таблицу ulogin
				$id_member = $this->DB->buildAndFetch( array(
																'select' => 'member_id',
																'from' => 'members',
																'where' => "name='" .$Login21. "'" 
															) 
													 );
				
				$this->DB->insert( 'ulogin', array(
														'ident'	=> $ident,
														'seed' => $seed,
														'id_user' => $id_member['member_id'],
												   )
								 );
				
				//Выбор зарегестрированного пользователя для его последующей авторизации
				$result = $this->DB->buildAndFetch( array(
														'select' => 'id_user,seed',
														'from' => 'ulogin',
														'where' => "ident ='".$user['identity']."'" 
													) 
											);
				$Passwd=md5($ident.$user['last_name'].$result['seed']);
			}
			
			//Получение логина, пароля и емейла
			$result1 = $this->DB->buildAndFetch( array(
														'select' => '*',
														'from' => 'members',
														'where' => "member_id ='".$result['id_user']."'" 
													) 
											);
			$result2 = $this->DB->buildAndFetch( array(
														'select' => '*',
														'from' => 'profile_portal',
														'where' => "pp_member_id ='".$result['id_user']."'" 														
													) 
											);
			if(!$result2)
			{
				$this->DB->insert( 'profile_portal', array(
														'pp_member_id'	=> $result['id_user'],
														'pp_main_photo' => 'profile/'.md5($ident).'.jpg',
														'pp_main_width' => 100,
														'pp_main_height' => 100,
														'pp_thumb_photo' => 'profile/'.md5($ident).'.jpg',
														'pp_thumb_width' => 50,
														'pp_thumb_height' => 50,
														'avatar_location' => 'profile/'.md5($ident).'.jpg',
														
												   )
								 );
			}
			$username=$result1['name'];
			$email_address = $result1['email'];
			$password=$Passwd;

			return $this->authLocal( $username, $email_address, $password );
			$this->auth_errors = array();
			if ( $this->return_code == 'SUCCESS' )
  			{
				return true;
  			}
		}
	}