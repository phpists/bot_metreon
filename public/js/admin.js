$(document).ready(function(){
    regenerate();
});

function regenerate(){
    var btn = $('#btn_regenerate');
    
    if(btn.length > 0){
        var lock = false;
        
        btn.off();
        btn.unbind();
        
        btn.on('click', function(e){
            e.preventDefault();
            
            if(!lock){
                lock = true;
                
                btn.addClass('disable');
                
                $.ajax({
                    url: '/ajax/admin/jslangs',
                    type: "get",
                    dataType: "json",
                    success: function(resp) {
                        lock = false;
                        btn.removeClass('disable');
                        
                        if(resp.status){
                            alert('Файл успешно обновлен');
                        }else{
                            alert('Произошла ошибка');
                        };
                    },
                    error: function(){
                        lock = false;
                        btn.removeClass('disable');
                        
                        alert('Произошла ошибка');
                    }
                });
            };
            
            return false;
        });
    }
};