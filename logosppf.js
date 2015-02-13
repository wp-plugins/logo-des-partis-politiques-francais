jQuery(document).ready(function($){
    $('.logosppf_nojs').hide();
    $('.logosppf-ban-item').click(function(){
       name = $(this).text();
       bans = $('#logosppf_ban').val();
       // Is banned
       if(bans.indexOf(','+name)>-1 || bans.indexOf(name+',')>-1){
           bans = bans.replace(','+name,'').replace(name+',','');
           $('#logosppf_ban').val(bans);
           $(this).removeClass('banned');
       }
       else{
           $('#logosppf_ban').val(bans+','+name);
           $(this).addClass('banned');
       }
    });
    $('.logosppf-color-picker ').wpColorPicker();
});