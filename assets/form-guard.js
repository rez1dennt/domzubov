(function(root,factory){const api=factory();if(typeof module==='object'&&module.exports)module.exports=api;else root.DZFormGuard=api;})(typeof globalThis!=='undefined'?globalThis:this,function(){
  'use strict';
  const fail=(field,error)=>({ok:false,field,error});
  function validate(data){
    const name=String(data.name||'').trim(),rawPhone=String(data.phone||'').trim(),email=String(data.email||'').trim(),message=String(data.message||'').trim();
    if(String(data.website||'').trim())return fail('name','Не удалось подготовить обращение. Позвоните в клинику или воспользуйтесь онлайн-записью.');
    if(name.length<2||name.length>80||!/^\p{L}[\p{L}\p{M} .’'\-]*$/u.test(name))return fail('name','Введите имя: от 2 до 80 символов, без ссылок и специальных команд.');
    let digits=rawPhone.replace(/\D/g,'');if(digits.startsWith('8'))digits='7'+digits.slice(1);
    if(!/^[+\d() \-]+$/.test(rawPhone)||!/^7\d{10}$/.test(digits))return fail('phone','Введите полный номер: +7 и десять цифр.');
    if(email&&(email.length>254||!/^\S+@[^\s@]+\.[^\s@]+$/.test(email)||/[\x00-\x1F\x7F<>]/.test(email)))return fail('email','Проверьте адрес электронной почты.');
    if(message.length>2000||/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/.test(message))return fail('message','Комментарий должен содержать не более 2000 символов.');
    if(data.consent!==true)return fail('consent','Подтвердите ознакомление с условиями обработки данных.');
    return {ok:true,name,phone:'+'+digits,email,message};
  }
  function mailto(data){
    if(!data.ok)throw new Error('Invalid form data');
    const lines=[`Имя: ${data.name}`,`Телефон: ${data.phone}`];
    if(data.email)lines.push(`Email: ${data.email}`);if(data.message)lines.push(`Комментарий: ${data.message}`);
    return 'mailto:'+encodeURIComponent('Domzubov777@yandex.ru')+'?subject='+encodeURIComponent('Запрос с сайта «Дом Зубов»')+'&body='+encodeURIComponent(lines.join('\n'));
  }
  return {validate,mailto};
});
