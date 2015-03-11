$(document).ready(function(){
	var options = {
		firstStep:'step1',
		error_messages:[],
		defaults:{
			save_url: '/res_save/',
			onSuccessSubmit:function(data){
				this.message('success','Данные сохранены','');
			},
			message:function(type,str,title){
					var opts = {
					 title: title,
					 text: str,
					 type:type,
					 width: '600px',
					 history:false
					};
				$.pnotify(opts);
			}
		},
		handlers:{
			step1:{
				construct:function(o,obj){
					$.pnotify.defaults.styling = "jqueryui";
				},
				init:function(o){
				},
				check:function(o,obj){
					return true;
				}
			}
		}
	}
	a = new fwizard(options);
	$('.fup').on('click',function(e,t){
			e.preventDefault();
			$('#rec_file').click();
			return false;
	});

});
