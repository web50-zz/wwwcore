
$(document).ready(function(){
	var sender = {
		init:function(){
			$.pnotify.defaults.styling = "jqueryui";
			var self = this;
			var btn = $('.ui-contacts .btn-submit');
			this.btn = btn;
			btn.on('click',function(e){
					e.preventDefault();
					self.send();
					return false;
				});
		},
		send:function(){
			var self = this;
			var frm = $('.ui-contacts form');
			var params =  frm.serialize();
			$.post(
				'./save/',
				params,
				function(data){
					if(data.success == true){
						self.message('success',data.message,'Сообщение');
						self.btn.hide();
					}else{
						self.message('error',data.message,'Ошибка');
					}

				}
			)
			return false;
		},
		message:function(type,str,title){
				var opts = {
			         title: title,
				 text: str,
				 type:type,
				 width: '300px',
				 history:false
			};
			$.pnotify(opts);
		},
	}
	sender.init();
})
