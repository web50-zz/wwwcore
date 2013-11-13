$(document).ready(function(){
	$('#www_response_form').submit(function(e){
		var frm = $(this);
		$.ajax({
			url: this.action,
			data: frm.serialize(),
			type: this.method || 'POST',
		}).done(function(res){
			console.log(res);
			// res.success - результат выполнения сабмита (boolean)
			// res.errors - массив ошибок (text)
			// res.fields - массив имён полей заполеннных с ошибкой (string)

			if (res && res.success == false){
				if (res.errors){
					alert(res.errors);
				}else{
					alert('Ошибка во время сохранения.');
				}
			}else{
				// Отзыв засабмитился
			}
		}).fail(function(){
			alert('Ошибка на сервере.');
		});
		return false;
	});
});
