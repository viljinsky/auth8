/**
 * Выравнивание формы по центру экрана
 * @param {type} form
 * @returns {undefined}
 */
function form_center(form){

    form.style.left= Math.floor((document.documentElement.clientWidth-form.clientWidth)/2)+'px';
    form.style.top=Math.floor((document.documentElement.clientHeight-form.clientHeight)/2)+'px';
}

function Request(callback){
    var request = new XMLHttpRequest();
    request.onreadystatechange=function(){
        if (request.readyState===4){
            switch (request.status){
                case 200:
                    callback(request.responseText);
                    return;
                case 404:
                    alert('Страница  не найдена ');
                    return;
                default:
                    alert('Ошибка : '+request.statusText);
            }
        }
    };
    return request;
}


function Form(option){
    var form = document.createElement('form');
    form.className='dialog-form';
    form.innerHTML='<div  class="dialog-form-title">'+option.title+'</div>'
                   +'<div class="dialog-form-content">' 
                   +option.content
                   +'</div>'
                   +'<div class="dialog-form-footer">'
                   +'<input type="submit" value="'+option.button+'">'
                   +'<button class="close-dialog">Закрыть</button></div>';
    document.body.appendChild(form);
    form_center(form);
    form.querySelector('.close-dialog').onclick=function(){
        form.close();
        return false;
    };
    
    if (option.focus!==undefined){
        form[option.focus].focus();
    };
    
    form.close = function(){
        document.body.removeChild(this);
    };
    return form;
}

/**
 *  Объект аутодентификации
 *  @admin_element дом-елемент - меню аудентификации
 *  @user_id    - текущий пользователь
 **/
function Auth(admin_element,user_id){

    /**путь к папке файлу auth_proc.php */
    var ADMIN_PATH = './libs/auth/auth_proc.php';

    var form = null;
    var self = this;
    
    /**путь к папке auth для captchar*/
    var admin_path= './libs/auth' ;
    
//    this.user_id = options.user_id;
    
    this.read_userinfo = function(user_id,callback){
      var request = Request(function(text){
           callback(JSON.parse(text));
      });
      request.open('POST',ADMIN_PATH);
      request.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
      request.send('command=userinfo&user_id='+user_id);
    };
    
    /** форма изменения ел.адреса*/
    this.email=function(){
        self.read_userinfo(user_id,function(user){
            if (user.error===0){
                form = new Form({
                    content: "<input name='user_id' hidden ><br>Укажите адрес электронной почты <input  name='email'>",
                    title  : "Изменение адреса",
                    button : "Выслать письмо"
                });
                form.user_id.value=user.user_id;
                form.email.value = user.email;
                form.onsubmit=function(){
                    confirm_email(form.user_id.value,form.email.value,function(message){
                        form.close();
                        alert(message);
                    });
                   return false;  
                };
            } else {
                alert (user.message);
            }
        });
    };
    
    this.forget=function(){
        form = Form({
            content:"Введите логин или email <input name='login_or_email' required><br>",
            title:  "Восстановление входа",
            button: "Восстановить",
            focus:  "login_or_email"
        });
        form.onsubmit=function(){
            var request = Request(function(text){
                if (text.length===0){
                    form.close();
                    alert('На электронный алрес, указанный \n\
                      Вами при регистрации отправлено письио с инструкциями');
                } else {
                    alert(text);
                }
            });
            var data = new FormData(this);
            data.append("command","forget");
            request.open('POST',ADMIN_PATH);
            request.send(data);
            return false;
        };
        return false;
    };
    
    /**
     * Функция кажется не используется !!!!
     * @param {type} login
     * @param {type} pwd
     * @param {type} callback
     * @returns {undefined}
     */
    this.remember=function(login,pwd,callback){
        var request = Request(function(text){
            console.log(text);
            var values = JSON.parse(text);
            if (values.error===0){
                location.reload();
            }
            if (values.error === 33){
                alert('Ошибка\n Mysql : "'+values.message+'"\nquery : "'+values.sql+"'");
            }
            if (callback!==null){
                callback(values);
            }
        });
        request.open('POST',ADMIN_PATH);
        request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        request.send('login='+ login+'&pwd='+pwd);
    };
    
    /**
     * Вход в пользователя
     * @returns {Boolean}
     */
    this.login=function(){
        form = Form({
            content :
                    '<table>'+    
                    '<tr><td>Логин или email</td><td><input name="login" placeholder="логин" requered></td></tr>'+
                    '<tr><td>Пароль</td><td><input name="password" type="password" placeholder="пароль" requered></td></tr>'+
                    '<tr><td>&nbsp;</td><td><input type="checkbox" name = "remember_me" checked value="true">Запомнить</td></tr>'+
                    '<tr><td colspan="2"><a href="#" data-action = "forget" >Забыли пароль (логин)?</a></td></tr>'+
//                    '<tr><td colspan="2"><a href="#" data-action = "dontmail">Не пришло письмо подтверждения?</a></td></tr>'+
                    '</table>',
           title    :"Вход пользователя",
           button   :"Войти",
           focus    :"login"
        });
        
        form.onclick=function(event){
            if (event.target.tagName==='A' && event.target.hasAttribute('data-action')){
                var action = event.target.getAttribute('data-action');

                switch (action){
                    case 'forget':
                      form.close();
                      self.forget();
                      return false;
                    case 'dontmail':
                      form.close();
                      self.email();
                      return false;
                }
            }
        };
//        form.action
        form.onsubmit = function(){
            var request = Request(function(text){
                console.log('login responce = "'+text+'"');
                    var values = JSON.parse(text);
                    switch (values.error){
                        case 0:
                            user_id=values.user_id;
                            form.close();
                            location.reload();
                            return;
                        // Аддрес не подтверждён    
                        case 37:
                            user_id=values.user_id;
                            alert(values.message)
//                            form.close();
                            return false;
                        default :   
                            alert(' '+values.error+' '+values.message);
                    }
                
            });
            var data = new FormData(this);
            data.append('command','login');
            request.open('POST',ADMIN_PATH);
            request.send(data);
            return false;
        };
        return false;        
    };
    
    /** Выход пользователя*/
    this.logout=function(){
        var request = Request(function(text){
            document.location.reload();            
        });
        request.open('POST',ADMIN_PATH);
        request.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
        request.send('command=logout');
        return false;
    };
    
    
    /** Информация о пользователе */
    this.userinfo=function(){
        
        self.read_userinfo(user_id,function(query){
            form = Form({
                content :
                         '<table>'
                       + '<tr><td>Логин</td><td><input name="login" readonly></td></tr>'
                       +'<tr><td>email</td><td><input name="email" readonly></td></tr>'
                       +'<tr><td>Фамилия</td><td><input name="last_name"></td></tr>'
                       +'<tr><td>Имя</td><td><input name="first_name"></td></tr>'
                       +'<tr><td>Присылать мне новости</td><td><input type="checkbox" name="allow_to_notify" value="true"></td></tr>'
                       +'</table>',
                title   : "Информация о пользователе",
                button  : "Применить"
            });

            form.first_name.value = query.first_name;
            form.last_name.value = query.last_name;
            form.email.value = query.email;
            form.login.value = query.login;
            form.allow_to_notify.checked=query.allow_to_notify;


            form.onsubmit= function(){
                var request = Request(function(text){
                    console.log('userinfo : '+text);
                    var values = JSON.parse(text);
                    if (values.error===0){
                        form.close();
                        location.reload();
                        return;
                    };
                    alert(text);
                    
                });
                var data = new FormData(this);
                data.append('user_id',query.user_id);
                data.append('command','update_userinfo');
                request.open('POST',ADMIN_PATH);
                request.send(data);
                return false;
            };
            
        });
    };
    
    /**
     * Фукция вызывается из письма пользователя
     * @param {type} user_id
     * @param {type} hash
     * @returns {undefined}
     */
    this.confirm = function(user_id,hash){
        var request = Request(function(text){
            console.log(text);
            var a = JSON.parse(text);
            // нужно поблагодарить
            location.assign('./');
            alert(a['message']);
        });
        request.open("POST",ADMIN_PATH);
        request.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
        request.send('command=confirm&user_id='+user_id+'&hash='+hash);
    };
    
    this.register = function(){
        form = Form({
            title   :"Регистрация",
            button  :"Зарегистрироваться",
            content :
                '<input name="command" value="register" hidden>'    
                +'<table>'
                +'<tr><td>Имя</td> <td><input name="first_name" required></td></tr>'
                +'<tr><td>Фамилия</td> <td> <input name="last_name" required></td></tr>'
                +'<tr><td>Адрес эл.почты</td> <td> <input name="email" type="email" required></td></tr>'
                +'<tr><td>Логин</td> <td><input name="login" required></td></tr>'
                +'<tr><td>Пароль</td> <td><input name="password1" type="password" required></td></tr>'
                +'<tr><td>Пароль ещё раз</td> <td><input name="password2" type="password" required></td></tr>'
                +'<tr><td>&nbsp;</td><td><img src="'+admin_path+'/captcha.php" alt="captcha"></td></tr>'
                +'<tr><td>Число</td> <td><input name="captcha" required></td></tr>'
                +'</table>'
                +'<span class="comments"><strong>Внимание:<strong><br>На указанный элетронный адрес адрес будет отправлено письмо для завершения регистрации</span>',
            focus : "first_name"    

        });
        form.onsubmit= function(){
            
            var request = Request(function(text){
                console.log('Регистрация : '+text);
                var values = JSON.parse(text);
                if (values.error===0){
                    var message = 
                            "На указанный Вами e-mail в течении некоторого времени (несколько минут) придет подтверждение."
                           +"Если подтверждение вдруг не пришло, сделайте следующее:\n"
                           +" а) попробуйте проверить папку 'Спам' в Вашем почтовом ящике(иногда письма попадают туда)\n"
                           +" б) оставте сообщение на сайте с указаем фамилии имени и адреса\n"  
                           +" в) напишите письмо на timetabler@narod.ru с указанием Фамилии и Имени";
                    form.close();
                    alert(message);
                } else {
                    alert(values.message);
                }
            });
            
            request.open('POST',ADMIN_PATH);
            request.send(new FormData(this));
            return false;
        };
        return false;
    };
    
    
    this.message = function(){
        
        self.read_userinfo(user_id,function(data){
        
            form = Form({
                title   :"Сообщение",
                button  :"Отправить",
                content :
                    '<input name="command" value="message" hidden>'    
                    +'<table>'
                    +'<tr><td>Тема сообщения</td><td><select name="subject"></select></td></tr>'
                    +'<tr><td>От кого</td><td><input name="user_name" reqired></td></tr>'
                    +'<tr><td>Адрес эл.почты для ответа</td><td><input name="email" required></td></tr>'
                    +'<tr><td colspan="2">Текст сообщения</td></tr>'
                    +'<tr><td colspan="2"><textarea name="text" rows="5" style="width:100%" required></textarea></td></tr>'
                    +'<tr><td>&nbsp;</td><td><img src="'+admin_path+'/captcha.php" alt="captcha"></td></tr>'
                    +'<tr><td>Введите число</td><td><input name="captcha" required></td></tr>'
                    +'</table>',
                focus : "subject"    
            });
            
            form.subject.appendChild(new Option("Вопрос по программе"));
            form.subject.appendChild(new Option("Предложение по улучшению программы"));
            form.subject.appendChild(new Option("Предложение о сотрудничестве"));

            if ((typeof data.email)!='undefined'){
                form.email.value = data.email;
                form.user_name.value = data.last_name+' '+data.first_name;
            }

            form.onsubmit=function(){
                var request = Request(function(text){
                    console.log(text);
                    var values = JSON.parse(text);
                    if (values.error===0){
                        form.close();
                        alert('Сообщение успешно опрвалено');
                    } else {
                        alert(values.message);
                    }
                });
                request.open('POST',ADMIN_PATH);
                request.send(new FormData(this));
                return false;
            };
        });
        return false;
        
    };
    
    /**
     * Восстановление пароля
     * ---------------------
     * @param {type} query
     * @param {type} callback
     * @returns {undefined}
     */
    this.change= function(user_id,hash){
        form= Form({
            title   :"Введите новый пароль",
            button  :"Изменить",
            content : 
                   "<table>"
                   +"<tr><td>Новый пароль</td><td><input type='password' name='password1' required></td></tr>" 
                   +"<tr><td>Новый пароль (ещё раз)</td><td><input type='password' name='password2' required></td></tr>" 
                   +"<tr><td>&nbsp;</td><td>&nbsp;</td></tr>" 
                   +"</table>"
        });
        form.onsubmit=function(){
            var request = Request(function(text){
                if (text.length===0){
                    alert("Новый пароль вступил в действие");
                    location.assign('./');
                } else {
                    alert(text);
                    return false;
                }
            });
            var data = new FormData(this)
            data.append("command","restore");
            data.append("user_id",user_id);
            data.append("hash",hash);
            request.open('POST',ADMIN_PATH);
            request.send(data);
            return false;
        };
        
    };
    
    admin_element.onclick=function(event){
        var target = event.target;
        if (target.tagName==='A'){
            self[target.getAttribute('data-action')]();
            return false;
        }
    };
    

    var search = location.search;
    if (search.length>0){
        console.log(search);
        var param = {};
        
        param.getValue=function(key){
            for(k in this){
                if (k===key){
                    return this[k];
                }
            }
        };
        
        var tmp = search.slice(1).split('&');
        for (i=0;i<tmp.length;i++){
            var p = tmp[i].split('=');
            console.log(p[0]+' '+p[1]);
            param[p[0]]=p[1];
        };
        var  hash       = param.getValue('hash'),
             register   = param.getValue('register'),
             restore    = param.getValue('restore');
        
        
        if ((typeof restore !=='undefined')  && (typeof hash !=='undefined')){
            self.change(restore,hash);
        }
        
        if ((typeof register !== 'undefined')&&(typeof hash !== 'undefined')){
            self.confirm(register,hash);
        }
    }
    
     //-----------------------------------------------
     //            Списки пользователей
     // 
     //-----------------------------------------------
    
    
     var user_list_element ;
     
    /**
     * Применить изменения в форме разрешения
     * @param {type} form
     * @returns {undefined}
     */
    function permission_update(form){
        var data = new FormData(form)
        var request = Request(function(text){
            alert(text);
        });
        request.open('POST',ADMIN_PATH);
        data.append('command','permission_update');
        request.send(data);
    }
    
    /**
     * Открыть форму с разрешниями пользователя
     * @param {type} user_id
     * @returns {undefined}
     */
    this.user_permission= function(user_id){
        
        var request = Request(function(text){
            
            var form = document.createElement('form');
            form.className='dialog-form';
            form.innerHTML = 
                     '<div class="dialog-form-title">Разрешения</div>'
                    +'<div class="dialog-form-content">'+text+'</div>'
                    +'<div class="dialog-form-footer">'
                    +'  <input type="submit" value="Применить">'
                    +'  <input type="reset" value="Закрыть">'
                    +'</div>';
            
            document.body.appendChild(form);
            form_center(form);
            form.onreset = function(){
                document.body.removeChild(form);
                form=null;
                return false;
            };
            form.onsubmit = function(){
                permission_update(this);
                document.body.removeChild(form);
                form=null;
                return false;
            }
            
        });
        
        request.open('POST',ADMIN_PATH);
        request.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
        request.send('command=permission&user_id='+user_id);
        
        
    }
    
     
     function userlistclick(event){
        var target = event.target;
        var table,row,id;
        var action;
        if (target.tagName==='A'){
            if (target.hasAttribute('data-page')){
                auth.userlist(user_list_element,'page='+target.getAttribute('data-page'));
            } else  if (target.hasAttribute('data-action')){
                action = target.getAttribute('data-action');
                table =user_list_element.querySelector('table');
                row = target.closest('tr');
                id = row.getAttribute('data-id');
                switch(action){
                    case 'user':
                        self.user_permission(id);
                        break;
                    default:
                        alert(action+' - '+id);
                }
            }
            return false;
        }
        if (target.tagName==='BUTTON' && target.hasAttribute('data-action')){
            if (confirm('Удалить пользователя')){
                table = user_list_element.querySelector('table');
                action = target.getAttribute('data-action');
                row  = target.closest('tr');
                id = row.getAttribute('data-id');
                auth.delete_user(id,function(text){
                    if (text.length===0){
                        table.deleteRow(row.rowIndex);
                    } else {
                        var a = JSON.parse(text);
                        alert(a['message']);
                    }
                });
            }
            return false;
        }
    };


    /**
     * Поиск пользователя
     * @param {type} form
     * @param {type} output
     * @returns {undefined}
     */
    this.locate = function(form,output){
        var request = Request(function(text){
            output.innerHTML= text;
        });
        request.open('POST', ADMIN_PATH);// './libs/auth/auth_proc.php');
        var data = new FormData(form);
        data.append('command','locate');
        request.send(data);
   }
        
        
    
    
    this.userlist=function(element,page){
        if (typeof element === null){
            element=document.querySelector('#userlist');
        }
        user_list_element = element;
        user_list_element.onclick = userlistclick;
        
        var request = Request(function (text){
                element.innerHTML = '<h1>Список пользователей</h1>'+text;
        });
        console.log('typeof page :'+ typeof page);
        var params = 'command=userlist';
        if (typeof page !== 'undefined'){
            params += '&'+page;
        }
        
        request.open('POST',ADMIN_PATH);
        request.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
        request.send(params);
        
    };
    
    /**
     * Процедуры администратора
     * ------------------------
     * @param {type} user_id
     * @param {type} callback
     * @returns {undefined}
     */
    this.delete_user=function(user_id,callback){
        var request = Request(function(text){
            callback(text);            
        });
        request.open('POST',ADMIN_PATH);
        request.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
        request.send('command=delete&user_id='+user_id);
    };
    
}