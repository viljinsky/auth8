<?php



class Permission{
    
    public $user_id = 0;
    public $role_id = 0;
    public $banned  = false;
    
    public $a = array();
    
    function __construct($user_id) {
        
        $this->a = array();
        
        if (isset($user_id)){
            $this->user_id=  intval($user_id);

            $result = mysql_query("select role_id,banned from users where user_id=$user_id");
            if (mysql_num_rows($result)===0){
                $result = mysql_query('select permission_id from permission');
                while ($data=  mysql_fetch_array($result)){
                    $this->a[$data[0]]=false;
                }
                return;
            }

            $data = mysql_fetch_array($result);
            list($this->role_id,$this->banned) = $data;

            $result = mysql_query("select permission_id,permission_value,role_id from users_permission a inner join users u on a.user_id=u.user_id  where u.user_id=$user_id") or die(mysql_error());
            while ($data = mysql_fetch_array($result)){
                list($permission_id,$permission_value,$role_id)=$data;
                $this->a[$permission_id]=($permission_value && !$this->banned ) || ($this->role_id === ROLE_ADMIN)  ;
            }
        }
        
    }
    
    function get_role_list($selected){
        $str = '';
        $result= mysql_query("select role_id,role_name from user_role") or die(mysql_error());
        while ($data=  mysql_fetch_array($result)){
            list($role_id,$role_name)=$data;
            if ($role_id===$selected){
                $str .="<option value=$role_id selected>".$role_name."</option>";                
            } else  {
                $str .="<option value=$role_id >".$role_name."</option>";
            }
        }
        return $str;
    }
    
    function update(){
        
        $role_id    = filter_input(INPUT_POST, 'role_id');
        $confirmed  = filter_input(INPUT_POST, 'confirmed');
        $banned     = filter_input(INPUT_POST, 'banned');
        $email      = filter_input(INPUT_POST, 'email');
        if (!isset($banned)){
            $banned='false';
        }
        if (!isset($confirmed)){
            $confirmed='false';
        }
        
        mysql_query("update users set email='$email',email_confirmed=$confirmed,role_id=$role_id,banned=$banned where user_id=".$this->user_id) or die(mysql_error());
        
        $result = mysql_query("select permission_id,permission_name from permission") or die(mysql_error());
        while ($data = mysql_fetch_array($result)){
            list($id,$name)=$data;
            $value = filter_input(INPUT_POST, $name);
            if (!isset($value)) {
                $value='false';
            }
            mysql_query("update users_permission set permission_value = $value where user_id=".$this->user_id." and permission_id=$id ") or die(mysql_error());
        }
        
        $result = mysql_query("select * from v_users where user_id=$this->user_id") or die(mysql_error());
        
        $data = mysql_fetch_array($result);
        list($user_id,$user_name,$login,$role_name,$email,$confirmed,$reg_date,$last_visit,$visit_count)=$data;
        
        echo  "<td><input type='checkbox'></td><td>$role_name</td><td><a href='#' data-action='user'>$user_name</a></td><td>$login</td>"
            . "<td>$email</td><td>$reg_date</td><td>$last_visit</td><td>$visit_count</td>"
            ."<td><button data-action='delete'>Удалить</buttom></td><td><input type='checkbox' ".($confirmed?'checked':'')." disabled></td>";
    }
    
    function user_permission(){
        echo '<div>разрешения</div>';
        $result = mysql_query("select a.permission_id,a.permission_value,b.permission_description,b.permission_name from users_permission a inner join permission b on a.permission_id=b.permission_id  where a.user_id=$this->user_id limit 20") or die(mysql_error());
        echo '<table>';
        while ($data = mysql_fetch_array($result)){
            list($id,$value,$description,$name)=$data;
            echo '<tr><td>'.$id.'</td><td>'.$description.'</td><td>'.$value.'</td><td><input name="'.$name.'" type="checkbox" '.($value?'checked':'').' value="true"   ></td></tr>';
        }
        echo '</table>';
    }
    
    function user_visits(){
        echo '<div>Посещения</div>';
        echo '<table>';
        $result = mysql_query("select visit_time from visits where user_id=$this->user_id order by visit_time desc limit 10") or die(mysql_error());
        if (mysql_num_rows($result)===0){
                echo '<tr><td>-</td></tr>';            
        } else {
            while ($data = mysql_fetch_array($result)){
                echo '<tr><td>'.$data['visit_time'].'</td></tr>';
            }        
        }
        echo '</table>';
    }
    
    
    function edit(){
        $result = mysql_query("select concat(last_name,' ',first_name) ,reg_date,email,email_confirmed,role_id,banned "
                             ." from users where user_id=$this->user_id")
                or die(mysql_error());
        $data = mysql_fetch_array($result);
        list($user_name,$reg_date,$email,$email_confirmed,$role_id,$banned) = $data;
        echo '<table>'.        
             '<tr><td>Ид</td><td><input name="user_id" value="'.$this->user_id.'"></td></tr>'.
             "<tr><td colspan='2'>$user_name</td></tr>".
             "<tr><td colspan='2'><input name='email' value='".$email."'> <input name='confirmed' value='true' type='checkbox' ".($email_confirmed?'checked':'')."></td></tr>".   
             "<tr><td>Дата регистрации</td><td>$reg_date</td></tr>".
             "<tr><td>Статус</td><td><select name='role_id' id='role'>".$this->get_role_list($role_id)."</select></td></tr>".
            '</table>'.'<div><input name="banned" type="checkbox" value="true" '.($banned?'checked':'').'> заблокирован</div>';
        
        $this->user_permission();
        $this->user_visits();
    }
    
    function delete(){
        if (mysql_query('delete from users where user_id='.  $this->user_id)){
            return;
        }
        echo '{"error":'.ERROR_SQL.',"message":"'.mysql_error().'"}';
    }
    
    function userlist(){
        $count=15;
        $page = filter_input(INPUT_POST,'page');
        if (!isset($page)){
            $page=1;
        }

        $result = mysql_query("select count(*) from users") or die(mysql_error());
        $data=  mysql_fetch_array($result);
        $usercount = $data[0];
        $page_count = intval(($data[0]-1)/$count)+1;


        $start = ($page-1)*$count;

        $result = mysql_query("select * from v_users order by user_id limit $start,$count") or die(mysql_error());
        $html = '';

        $html .= '<table>';
        $recno = 0;
        while ($data = mysql_fetch_array($result)){
            list($user_id,$user_name,$login,$role_name,$email,$confirmed,$reg_date,$last_visit,$visit_count)=$data;
            $recno++;
            $html .='<tr data-id="'.$user_id.'">';
            $html .="<td><input type='checkbox'></td><td>$role_name</td><td><a href='#' data-action='user'>$user_name</a></td><td>$login</td>"
                . "<td>$email</td><td>$reg_date</td><td>$last_visit</td><td>$visit_count</td>"
                 ."<td><button data-action='delete'>Удалить</buttom></td><td><input type='checkbox' ".($confirmed?'checked':'')." disabled></td>";
            $html .='</tr>';
        }

        $html.='</table>';

        $first = $page>1?'<a href="#" data-page="1">Страница 1</a>...':'&nbsp;';
        $prior = $page>2?'<a href="#" data-page="'.($page-1).'" >Страница '.($page-1).'</a>':'&nbsp';
        $next  = $page<$page_count-1?'<a href="#" data-page="'.($page+1).'">Страница '.($page+1).'</a>':'&nbsp;';
        $last  = $page<$page_count?'...<a href="#" data-page="'.$page_count.'">Страница '.$page_count.'</a>':'&nbsp';

        $html.= '<div>всего пользователей <b>'.$usercount.'</b></div>';

        $html.= $first.$prior.'&nbsp;Страница '.$page.'&nbsp'.$next.$last;

        echo  '<div class="user-list-panel">'.$html.'</div>';
        
    }
    
}

