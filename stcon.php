<?php 
//資料庫以及登入判定
abstract class db{
	
	protected	$db_host = "34.80.32.119";
	protected	$db_username = "root";
	protected	$db_password = "a12345678";
	protected	$user,$pass,$username;
	protected	$st_con,$at_con,$bt_con;
	
		function st_connect(){
			$this->st_con=mysqli_connect($this->db_host,$this->db_username,$this->db_password,"student");
		}
		function at_connect(){
			$this->at_con= mysqli_connect($this->db_host,$this->db_username,$this->db_password,"ateacher");
		}
		function bt_connect(){
			$this->bt_con= mysqli_connect($this->db_host,$this->db_username,$this->db_password,"bteacher");
		}
	
		function login_yes(){
			echo "<script  language=javascript>
			alert('登入成功');
			location.href='http://localhost/%E5%B0%88%E9%A1%8C3.0/st/index.php?page=1';
			</script>";
		}
		function login_no(){
			echo "<script  language=javascript>
			alert('登入失敗');
			location.href='http://localhost/%E5%B0%88%E9%A1%8C3.0/index/';
			</script>";
		}
		function check_user(){
			session_start();
			if(is_null($_SESSION["st_user"]))
			{
			$this->login_no();
			}
		}
		
}
//登入
class login extends db{
		function __construct($user,$pass){
			$this->st_connect();
			$this->user = $user; 
			$this->pass = $pass; 
			$this->select();
		}
		function select(){
			$sql_login="SELECT * FROM student where user='$this->user' AND pass='$this->pass'";
			$row_result=mysqli_fetch_assoc(mysqli_query($this->st_con,$sql_login));
			if(empty($row_result))
    		{  
			$this->login_no();		
    		}  
    		else  
    		{  
			$this->username = $row_result["username"];
			$this->session();
    		} 
			
		}
		function session(){
			session_start();
			$_SESSION["st_user"] = $this->user;
			$_SESSION["st_pass"] = $this->pass;
			$_SESSION["st_username"] = $this->username;
			$this->login_yes();

		}
	}
class st extends db{
	private $page_name;
	function __construct($page_name){
			$this->check_user();
			$this->page_name = $page_name;
			$this->nav();
		}
	function nav(){
	echo "
	<nav class='navbar fixed-top navbar-expand-lg navbar-dark bg-dark fixed-top'>
    <div class='container'>
    <a class='navbar-brand' href='index.php?page=1'>嶺東推廣中心-學生</a>
    <button class='navbar-toggler navbar-toggler-right' type='button' data-toggle='collapse' data-target='#navbarResponsive' aria-controls='navbarResponsive' aria-expanded='false' aria-label='Toggle navigation'>
    <span class='navbar-toggler-icon'></span>
    </button>
    <div class='collapse navbar-collapse' id='navbarResponsive'>
       <ul class='navbar-nav ml-auto'>
         <li class='nav-item active'>
           <a class='nav-link' href='index.php?page=1'>主頁</a>
         </li>
         <li class='nav-item dropdown'>
           <a class='nav-link dropdown-toggle' href='#' id='navbarDropdownPortfolio' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>課程</a>
            <div class='dropdown-menu dropdown-menu-right' aria-labelledby='navbarDropdownPortfolio'>
              <a class='dropdown-item' href='class.php'>我的課程</a>
              <a class='dropdown-item' href='slect.php'>課程查詢</a>
              <a class='dropdown-item' href='need.php'>課程請願</a>
          	  <a class='dropdown-item' href='bb.php'>課程討論</a>
            </div>
         </li>
          <li class='nav-item'>
            <a class='nav-link' href='mynem.php'>我的成績</a>
          </li>
           <li class='nav-item'>
            <a class='nav-link' href='mydata.php'>我的資料</a>
          </li>
          <li class='nav-item dropdown'>
            <a class='nav-link dropdown-toggle' href='#' id='navbarDropdownBlog' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
              ".$_SESSION['st_username']."
            </a>
            <div class='dropdown-menu dropdown-menu-right' aria-labelledby='navbarDropdownBlog'>
				<a class='dropdown-item' href='loginout.php'>登出</a>
			</div>
          </li>
        </ul>
      </div>
    </div>
	</nav>
	<div class='container' style='background-color: #EFEFEF'>
    <h1 class='mt-4 mb-3'>
      <small></small>
    </h1>
	<ol class='breadcrumb'>
      <li class='breadcrumb-item'>
        <a href=''>學生</a>
      </li>
      <li class='breadcrumb-item active'>".$this->page_name."</li>
    </ol>";
		}
}
class index extends db{
	//placard//
	protected $pl_length='5';
	protected $pl_arrtot;
	protected $prevpage;
	protected $nextpage;
	//sql//
	protected $sql_pl_count;
	protected $sql_pl;
	protected $sql_hot_class;
	protected $sql_need_class;
	
		function __construct(){
			$this->at_connect();
			$this->bt_connect();
			$this->data();	
		}
		function data(){
			$this->sql_pl_count = "SELECT * FROM `placard`";
			$this->sql_hot_class = "SELECT * FROM `teacher`,`name_teacher` INNER JOIN `classroom` WHERE classroom.status='已通過' ORDER BY `classroom`.`number` DESC LIMIT 0,4";
			$this->sql_need_class = "SELECT * FROM `class_need` ORDER BY `hot` DESC LIMIT 0,4";
			$this->pl_arrtot = mysqli_num_rows(mysqli_query($this->bt_con,$this->sql_pl_count));
		}
		function placard($page){
			$pagenum=$page;
			$pagetot=ceil($this->pl_arrtot/$this->pl_length);
			if($pagenum>=$pagetot){
			$pagenum=$pagetot;
			}	
			$offset=($pagenum-1)*$this->pl_length;
			$this->sql_pl="select * from placard order by date DESC limit {$offset},{$this->pl_length}";  
			$this->prevpage=$pagenum-1;
			$this->nextpage=$pagenum+1;
			
			$result = mysqli_query($this->bt_con,$this->sql_pl);
			while($row_result=mysqli_fetch_assoc($result)){
			echo 
			"<tr>
			<td><a href='placard.php?id=".$row_result['placard_id']."'>".$row_result['theme']."</td>
			<td>".$row_result['date']."</td>
			</tr>";
			}
		}
		function placard_page($page){
			$num_rows = mysqli_num_rows(mysqli_query($this->bt_con,$this->sql_pl));
			$ii=ceil($num_rows/$this->pl_length+1);
			echo "<li> <a href='index.php?page={$this->prevpage}'>&laquo;</a></li>";
				for ( $i=1 ; $i<=$ii ; $i++ )	
				{
				echo  "<li><a href='index.php?page={$i}'>$i</a></li>";
				}
			echo "<li><a href='index.php?page={$this->nextpage}'>&raquo;</a></li>";
		}
		function hot_class(){
			$result = mysqli_query($this->at_con,$this->sql_hot_class);	
			while($row_result=mysqli_fetch_assoc($result)){
			echo " 
      		<div class='col-lg-3 mb-3'>
			<div class='card h-100 text-center'>
			<img class='card-img-top' src='img/8.png' alt=''>
        	<div class='card-body'>
				<h4 class='card-title'>".$row_result["class_name"]."</h4>
            	<h6 class='card-subtitle mb-2 text-muted'>星期".$row_result["week"].'-第'."".$row_result["time"].'節'.'-第'.$row_result["time_up"].'節'."</h6>
            	<p class='card-text'>".$row_result["Introduction"]."</p>
			</div>
        	<div class='card-footer'>開課教師:<a href='#'>".$row_result["name"]."</a>
			</div>
        	</div>
      		</div>";
			}
		}
		function need_class(){
			$result = mysqli_query($this->at_con,$this->sql_need_class);	
			while($row_result=mysqli_fetch_assoc($result)){
			echo " 
      		<div class='col-lg-3 mb-3'>
        	<div class='card h-100 text-center'>
			<img class='card-img-top' src='8.png' alt=''>
          	<div class='card-body'>
            <h4 class='card-title'>".$row_result["class_name"]."</h4>
 			<p class='card-text'>".$row_result["Introduction"]."</p>
			</div>
          	<div class='card-footer'>
			<a href='need.php' class='btn btn-primary'>熱度:".$row_result["hot"]."</a>
          	</div>
			</div>
			</div>";
			}
		}
}
class my_class extends db{
	//sql//
	protected $sql_my_class;
	
		function __construct(){
		$this->at_connect();
		$this->data();
		$this->select();
		}
		function data(){
		$this->sql_my_class = "SELECT * FROM student.class,ateacher.classroom,ateacher.name_teacher WHERE student.class.status='已通過' AND ateacher.classroom.class_id=student.class.class_id AND student.class.user='".$_SESSION["st_user"]."'AND ateacher.classroom.aid=ateacher.name_teacher.aid";
		}
		function select(){
		$result = mysqli_query($this->at_con,$this->sql_my_class);	
		while($row_result=mysqli_fetch_assoc($result)){
   		echo 
		"<div class='card mb-4'>
      		<div class='card-body'>
        		<div class='row'>
          		<div class='col-lg-6'>
            		<a href='#'><img class='img-fluid rounded' src='img/p1.png' alt=''></a>
          		</div>
          			<div class='col-lg-6'>
            			<h2 class='card-title'>".$row_result["class_name"]."/星期".$row_result["week"].'第'."".$row_result["time"].'節到第'.$row_result["time_up"].'節'."</h2>
						<h4 class='card-title'>指導老師:".$row_result["name"]."</h4>
			  			<h4 class='card-title'>上課地點:".$row_result["location"]."</h4>
            			<p class='card-text'>".nl2br($row_result['Introduction'])."</p>
            			<a href='de.php?id=".$row_result["class_id"]."' class='btn btn-danger'>退選</a>
          			</div>
        		</div>
				</div>
			<div class='mb-4' id='accordion' role='tablist' aria-multiselectable='true'>
    			<div class='card'>
        		<div class='card-header' role='tab' id='headingOne'>
          		<h5 class='mb-0'>
				<a data-toggle='collapse' data-parent='#accordion' href='#A".$row_result["class_name"]."A' aria-expanded='true' aria-controls='collapseOne'>成績分配</a>
          		</h5>
        	</div>
			<div id='A".$row_result["class_name"]."A' class='collapse' role='tabpanel' aria-labelledby='headingOne'>
				<div class='card-body'>平時成績".$row_result["usually_test"]."%<br>期中考".$row_result["mid_test"]."%<br>期末考".$row_result["last_test"].'%'."</div>
        	</div>
      		</div>
		</div>
	   	<div class='card'>
        	<div class='card-header' role='tab' id='headingTwo'>
				<h5 class='mb-0'>
				<a class='collapsed' data-toggle='collapse' data-parent='#accordion' href='#A".$row_result["class_name"]."B' aria-expanded='false' aria-controls='collapseTwo'>課程規劃
          	</h5>
        </div>
        <div id='A".$row_result["class_name"]."B' class='collapse' role='tabpanel' aria-labelledby='headingTwo'>
          <div class='card-body'>".nl2br($row_result['plan'])."</div>
        </div>
      	</div>
	  	</div>";
			}
		}
}
class my_select extends db{
	//sql//
	protected $sql_my_select;	
	
		function __construct(){
			$this->at_connect();
			$this->data();
			$this->select();
		}
		function data(){
			$bb=$_GET["priority"];
			$cl=$_GET["class"];
	  		$te=$_GET["te"];
	  		$da=$_GET["dat"];
				if($_GET["priority"]=='1'){
					$vi="status='已通過'";
					}elseif($_GET["priority"]=='2'){
						$vi="status='已通過' AND class_name='$cl'";
					}elseif($_GET["priority"]=='3'){
						$vi="status='已通過' AND name='$te'";
					}elseif($_GET["priority"]=='4'){
						$vi="status='已通過' AND week='$da'";
					}
			$this->sql_my_select = "SELECT * FROM `classroom`,`name_teacher` WHERE $vi  AND classroom.aid=name_teacher.aid";
		}
		function select(){
			$result = mysqli_query($this->at_con,$this->sql_my_select);
			while($row_result=mysqli_fetch_assoc($result)){
			echo 
			"<div class='col-lg-4 mb-4'>
        		<div class='card h-100'>
          			<h4 class='card-header'>".$row_result["class_name"]."/星期".$row_result["week"].'/第'."".$row_result["time"].'節'.'-第'.$row_result["time_up"]."</h4>
          		<div class='card-body'>
				<p class='card-text'>上課地點:".$row_result["location"]."</p>
            	<p class='card-text'>".nl2br($row_result['Introduction'])."</p>
				</div>
          <div class='card-footer'>
		  		<a href='#' class='btn btn-warning'>指導老師:".$row_result["name"]."</a>
            	<a href='check.php?id=".$row_result["class_id"]."' class='btn btn-primary'>選課</a>
		</div>
        		</div>
      	</div>";
		}
		}  
}
class class_need extends db{
	//sql//
	protected $sql_class_need_select;
	
		function __construct(){
			$this->at_connect();
			$this->data();
		}
		function data(){
			$this->sql_class_need_select = "SELECT * FROM `class_need` ORDER BY `hot` DESC";
		}
		function select(){
			$result = mysqli_query($this->at_con,$this->sql_class_need_select);
			while($row_result=mysqli_fetch_assoc($result)){
			echo 
			"<div class='col-lg-4 mb-4'>
			<div class='card h-100 text-center' ><img class='card-img-top' src='img/7.png' alt=''>
          		<h4 class='card-header'>".$row_result["class_name"]."</h4>
          	<div class='card-body'>
				<p class='card-text'>".nl2br($row_result['Introduction'])."</p>
			</div>
          	<div class='card-footer'>
            	<a href='hotup.php?id=".$row_result["class_need_id"]."' class='btn btn-primary'>支持</a> <a href='#' class='btn btn-danger'>熱度".$row_result["hot"]."</a>
          	</div>
        	</div>
      		</div>";
			}
		}
}
class st_talk extends db{
	//sql//
	protected $sql_st_nowclass;
	protected $sql_st_closeclass;
	protected $sql_st_check;
	protected $sql_st_title;
	protected $sql_st_talk;
	protected $sql_st_theme;
	protected $sql_st_RE_theme;
	protected $sql_st_droplist;
	
		function __construct(){
			$this->at_connect();
			$this->st_connect();
			$this->data();
		}
		function data(){
			if(!empty($_GET['id']))
				{
				$this->sql_st_check = "SELECT * FROM `class` WHERE `class_id`='".$_GET['id']."' AND `user`='".$_SESSION["st_user"]."'";
				$this->sql_st_title="SELECT * FROM `classroom` WHERE `class_id`='".$_GET['id']."'";
				$this->sql_st_talk="SELECT * FROM mengess,classroom WHERE mengess.class_id = '".$_GET['id']."'AND classroom.class_id='".$_GET['id']."'";
				$this->check();
				}
			if(!empty($_GET['me_id'])){
				$this->sql_st_theme = "SELECT * FROM `mengess` WHERE mengess_id='".$_GET['me_id']."'";
				$this->sql_st_RE_theme = "SELECT * FROM `re_mengess` WHERE `mengess_id`='".$_GET['me_id']."'";
				$this->check();
				}
			$this->sql_st_nowclass = "SELECT * FROM student.class,ateacher.classroom,ateacher.name_teacher WHERE student.class.status='已通過' AND ateacher.classroom.class_id=student.class.class_id AND student.class.user='".$_SESSION["st_user"]."'and ateacher.classroom.aid=ateacher.name_teacher.aid";
			$this->sql_st_closeclass ="SELECT * FROM student.class,ateacher.classroom,ateacher.name_teacher WHERE student.class.status='已結束' AND ateacher.classroom.class_id=student.class.class_id AND student.class.user='".$_SESSION["st_user"]."'and ateacher.classroom.aid=ateacher.name_teacher.aid";
			$this->sql_st_droplist = "SELECT * FROM student.class,ateacher.classroom where student.class.user='".$_SESSION["st_user"]."' AND student.class.status='已通過' AND ateacher.classroom.class_id=student.class.class_id";
			
		}
		function check(){
			$result = mysqli_query($this->st_con,$this->sql_st_check);
			$row_result=mysqli_fetch_assoc($result);
			if($row_result['user']!=$_SESSION["st_user"]){
			echo 
			"<script  language=javascript>
			location.href='index.php?page=1';
			</script>";
			}	
		}
		function title(){
			$result = mysqli_query($this->at_con,$this->sql_st_title);
			$row_result=mysqli_fetch_assoc($result);
			echo $row_result["class_name"];
		}
		function select_nowclass(){
			$result = mysqli_query($this->st_con,$this->sql_st_nowclass);
			if($_SERVER["PHP_SELF"]=='/專題3.0/st/mynem.php'or $_SERVER["PHP_SELF"]=='/專題3.0/st/mynem2.php'){
				while($row_result=mysqli_fetch_assoc($result)){
				echo "<a href='mynem2.php?id=".$row_result["class_id"]."' class='list-group-item'>".$row_result["class_name"]."</a>";
				}
			}else{
				while($row_result=mysqli_fetch_assoc($result)){
				echo "<a href='bb2.php?id=".$row_result["class_id"]."' class='list-group-item'>".$row_result["class_name"]."</a>";
				}
			}
		}
		
		function select_closeclass(){
			$result = mysqli_query($this->st_con,$this->sql_st_closeclass);
				if($_SERVER["PHP_SELF"]=='/專題3.0/st/mynem.php' or $_SERVER["PHP_SELF"]=='/專題3.0/st/mynem2.php'){
				while($row_result=mysqli_fetch_assoc($result)){
			echo "<a href='mynem2.php?id=".$row_result["class_id"]."' class='list-group-item'>".$row_result["class_name"]."</a>";
				}
			}else{
				while($row_result=mysqli_fetch_assoc($result)){
				echo "<a href='bb2.php?id=".$row_result["class_id"]."' class='list-group-item'>".$row_result["class_name"]."</a>";
				}
			}
		}
		function talk(){
			$result = mysqli_query($this->at_con,$this->sql_st_talk);
			while($row_result=mysqli_fetch_assoc($result)){
			echo"
		 	<div class='card mb-4'>
				<div class='card-body'>
            	<h2 class='card-title'>".$row_result["title"]."(".$row_result["pepole"].")</h2>
            	<p class='card-text'>".$row_result["text"]."</p>
            	<a href='bb3.php?me_id=".$row_result["mengess_id"]."&id=".$row_result["class_id"]."' class='btn btn-primary'>查看討論</a>
          		</div>
          	<div class='card-footer text-muted'>".$row_result["writer_username"]."</div> 
		  	</div>"
				;}
		}
		function title_talk(){
			$result = mysqli_query($this->at_con,$this->sql_st_theme);
			$row_result=mysqli_fetch_assoc($result);
			echo "
			<h2>標題:".$row_result['title']."</h2> 
			<p>作者:".$row_result['writer_username']."</p><hr><p>";
			echo nl2br($row_result['text']);
			echo "</p><hr>";
		}
		function Re_title_talk(){
			$result = mysqli_query($this->at_con,$this->sql_st_RE_theme);
			while($row_result=mysqli_fetch_assoc($result)){
			echo "
        	<div class='media mb-4'>
          	<img class='d-flex mr-3 rounded-circle' src='http://placehold.it/50x50' alt=''>
			<div class='media-body'>
		  	<hr>
            <h5 class='mt-0'>回覆者:".$row_result['writer_user']."</h5>";
			echo nl2br($row_result['text']);
			echo"
          	</div>
        	</div>
			<hr>";
			}
		}
		function droplist(){
			$result = mysqli_query($this->at_con,$this->sql_st_droplist);
			while($row_result=mysqli_fetch_assoc($result)){
			echo "<option value='".$row_result['class_id']."'>".$row_result['class_name']."</option>";
			}
		}
		function new_talk(){
			$n=$_POST['n0'];
			$n1=$_POST['n1'];
			$n2=$_POST['n2'];
			$n3=$_POST['n3'];
			$sql ="INSERT INTO `mengess` (`class_id`,`title`, `writer_user`, `text`,`writer_identity`,`writer_username`) VALUES ('$n','$n1','$n2','$n3','student','".$_SESSION['st_username']."')";
			mysqli_query($this->at_con, $sql);
		}
		function Re_talk(){
			$ss=$_POST['hi'];
			$id=$_POST['h2'];
			$sql ="INSERT INTO `re_mengess` (`writer_user`, `text`,";
			$sql.="`mengess_id`,`writer_id`,`writer_identity`) VALUES ('";
			$sql.=$_POST["h3"]."','".$_POST["hi"]."','";
 			$sql.=$_POST["h2"]."','".$_SESSION['st_user']."','student')";
			mysqli_query($this->at_con, $sql);
			$sql2 ="UPDATE mengess SET `pepole`=`pepole`+1 WHERE mengess_id=$id";
			mysqli_query($this->at_con, $sql2);
		}

}
class st_nem extends db{
	//sql//
	protected $sql_select_nem;
	
		function __construct(){
			$this->st_connect();
			$this->data();
		}
		function data(){
			 $this->sql_select_nem= "SELECT * FROM student.number,ateacher.classroom where student.number.class_id='".$_GET["id"]."' AND student.number.user='".$_SESSION["st_user"]."' AND student.number.class_id=ateacher.classroom.class_id";
		}
		function select(){
			$result = mysqli_query($this->st_con,$this->sql_select_nem);
			while($row_result=mysqli_fetch_assoc($result)){
			echo "<tr>
			<td>".$row_result["teacher"]."</td>
			<td>".$row_result["st_mid"]."</td>
			<td>".$row_result["st_last"]."</td>
			<td>".$row_result["st_usually"]."</td>
			<td>".$row_result["st_total"]."</td>
			</tr>";
				}
			}
		}
class st_data extends db{
		//sql//
	protected $sql_st_data;
	
		function __construct(){
			$this->st_connect();
			$this->data();
		}
		function data(){
			 $this->sql_st_data= "SELECT * FROM `student`,`st_data` WHERE student.sid=st_data.sid AND user='".$_SESSION["st_user"]."'";
		}
		function select(){
			$result = mysqli_query($this->st_con,$this->sql_st_data);
			while($row_result=mysqli_fetch_assoc($result)){
			echo"
			<div class='row uniform 50%'>
				<div class='6u 12u$(4)'>
				<h4>姓名</h4>
				<input type='text' name='name' id='name' value='".$row_result["username"]."' placeholder=''  readonly='readonly'/>
				</div>
				<div class='6u$ 12u$(4)'>
				<h4>姓別</h4>
				<input type='text' name='name' id='name' value='".$row_result["sex"]."' placeholder=''  readonly='readonly'/>
				</div>
				<div class='6u 12u$(4)'>
				<h4>身分證字號</h4>
				<input type='text' name='name' id='name' value='".$row_result["idcard"]."' placeholder='' readonly='readonly' />
				</div>
				<div class='6u$ 12u$(4)'>
				<h4>血型</h4>
				<input type='text' name='name' id='name' value='".$row_result["blood"]."' placeholder='' readonly='readonly' />
				</div>
				<div class='6u 12u$(4)'>
				<h4>生日</h4>
				<input type='text' name='name' id='name' value='".$row_result["birthday"]."' placeholder='' readonly='readonly'/>
				</div>
				<div class='6u 12u$(4)'>
				<h4>住家電話</h4>
				<input type='text' name='name' id='name' value='".$row_result["phone-house"]."' placeholder='' readonly='readonly'/>
				</div>
				<div class='6u 12u$(4)'>
				<h4>行動電話</h4>
				<input type='text' name='name' id='name' value='".$row_result["phone"]."' placeholder='' readonly='readonly'/>
				</div>
				<div class='6u 12u$(4)'>
				<h4>信箱</h4>
				<input type='text' name='name' id='name' value='".$row_result["mail"]."' placeholder='' readonly='readonly'/>
				</div>
				<div class='6u 12u$(4)'>
				<h4>連絡地址</h4>
				<input type='text' name='name' id='name' value='".$row_result["address"]."' placeholder='' readonly='readonly'/>
				</div>
				<div class='6u 12u$(4)'>
				<h4>最高學歷校名</h4>
				<input type='text' name='name' id='name' value='".$row_result["school"]."' placeholder='' /readonly='readonly'>
				</div>
			</div>";
				}
			}
}		
class check extends db{
//sql//
	protected $sql_class_insert;
	protected $sql_class_check;
	
		function __construct(){
			$this->st_connect();
			$this->data();
			$this->insert();
		}
		function data(){
			 $this->sql_class_check= "SELECT * FROM `class` WHERE user='".$_SESSION['st_user']."' AND class_id='".$_GET['id']."'";
			 $this->sql_class_insert= "INSERT INTO `class` (`class_id`, `user`, `status`) VALUES ('".$_GET['id']."', '".$_SESSION['st_user']."', '審核中');";
		}
		function insert(){
			$result = mysqli_query($this->st_con,$this->sql_class_check);
			if(empty(mysqli_num_rows($result))){
				$result = mysqli_query($this->st_con,$this->sql_class_insert);
				echo "<script  language=javascript>
				alert('選課成功');
				location.href='slect.php';
				</script>";
			}else{
				echo "<script  language=javascript>
				alert('這門課已經選過了');
				location.href='slect.php';
				</script>";
			}
		}
}										
class creat_needclass extends db{
//sql//
	protected $sql_needclass_insert;
	protected $sql_needclass_check;
	
		function __construct(){
			$this->at_connect();
			$this->data();
			$this->insert();
		}
		function data(){
			$n11=$_GET["n1"];
			$n22=$_GET["n2"];
			$this->sql_needclass_check="SELECT * FROM `class_need` WHERE class_name ='$n11'";
			$this->sql_needclass_insert="INSERT INTO `class_need` (`class_name`, `Introduction`) VALUES ('$n11', '$n22')";
		}
		function insert(){
			$result = mysqli_query($this->at_con,$this->sql_needclass_check);
			if(empty(mysqli_num_rows($result))){
				$result = mysqli_query($this->at_con,$this->sql_needclass_insert);
				echo "<script  language=javascript>
				alert('請願成功');
				location.href='need.php';
				</script>";
			}else{
				echo "<script  language=javascript>
				alert('已經有人請願這門課了');
				location.href='need.php';
				</script>";
			}
		}
}			
class out_class extends db{
	//sql//
	protected $sql_needclass_insert;
	protected $sql_needclass_check;
	
		function __construct(){
			$this->st_connect();
			$this->at_connect();
			$this->data();
			$this->confirm();
		}
		function data(){
			$this->sql_out_class="DELETE FROM `class` WHERE `class_id`='".$_GET['id']."'";
			$this->sql_out_class2="UPDATE `classroom` SET `number`=`number`-1 WHERE `class_id`='".$_GET['id']."'";
		}
		function confirm(){
			$result = mysqli_query($this->st_con,$this->sql_out_class);
			$result = mysqli_query($this->at_con,$this->sql_out_class2);
		}
}
class hot extends db{
	//sql//
	protected $sql_hot;
	
		function __construct(){
			$this->at_connect();
			$this->data();
			$this->update();
		}
		function data(){
			$this->sql_hot="UPDATE `class_need` SET hot=hot+1 WHERE class_need_id='".$_GET["id"]."'";
		}
		function update(){
			$result = mysqli_query($this->at_con,$this->sql_hot);
		}
}
class placard extends db{
	//sql//
	protected $sql_placard;
	
		function __construct(){
			$this->bt_connect();
			$this->data();
			$this->select();
		}
		function data(){
			$this->sql_placard="SELECT * FROM `placard`,`teacher` WHERE placard_id ='".$_GET['id']."'AND teacher.bid=placard.bid";
		}
		function select(){
			$result = mysqli_query($this->bt_con,$this->sql_placard);
			$row_result=mysqli_fetch_assoc($result);
			echo "<h2>標題:".$row_result['theme']."</h2> 
			<p>作者:".$row_result['username']."</p><hr><p>";  
			echo nl2br($row_result['content']);
		}
}
?>