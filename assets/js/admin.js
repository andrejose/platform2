$(function(){

	$('.btn-add-session').on('click', function(){
		var html = $('#session-model').html();
			html = $.parseHTML(html);

		var dateObj = new Date();
        var hour = dateObj.getHours();
        var minute = dateObj.getMinutes();
        var hourMinute =  (dateObj.getHours() < 10 ? '0' : '') + dateObj.getHours() + ':' + (dateObj.getMinutes() < 10 ? '0' : '') + dateObj.getMinutes();

        $.ajax({
        	url 	: 'admin.php',
        	type 	: 'post',
        	dataType: 'json',
        	data 	: {
        		'hour' 	: hour,
        		'minute': minute,
        		'action': 'createSession'
        	},
        	success : function(data){
        		if (data.result == 'success') {
        			// Add the ID for the session element
        			$(html).attr('data-id', data.id);
        			// Add the ID for hour and minute elements
        			$(html).find('select[name="hour"]')
        				.attr('id', 'hour'+data.id)
        				.attr('session-id', data.id)
        				.val(hour)
        				.prev('label').attr('for', 'hour'+data.id);
        			$(html).find('select[name="minute"]')
        				.attr('id', 'minute'+data.id)
        				.attr('session-id', data.id)
        				.val(minute)
        				.prev('label').attr('for', 'minute'+data.id);
        			$(html).find('.hour').html(hourMinute);

        			$('#main').append(html);

					setEvents();

        		} else {
        			alert('Error on save session.');
        		}
        	}
        });
	});

    $('.btn-clear-cache').on('click', function(){
        localStorage.clear();
    });

	setEvents();

    $('#summernote').summernote({
        height: 200
    });

});

function setEvents() {

	$('.btn-add-questionnaire').off('click').on('click', function(){

		var html = $('#questionnaire-model').html();
			html = $.parseHTML(html);

		var sessionId = $(this).parents('.session').attr('data-id');
		var sessionElement = $('.session[data-id="'+sessionId+'"]');

        $.ajax({
        	url 	: 'admin.php',
        	type 	: 'post',
        	dataType: 'json',
        	data 	: {
        		'session_id' : sessionId,
        		'action': 'createQuestionnaire'
        	},
        	success : function(data){
        		if (data.result == 'success') {
        			// Add the ID for the session element
        			$(html).attr('data-id', data.id);

        			// Add the ID for hour and minute elements
        			$(html).find('input[name="title"]')
        				.attr('id', 'qTitle'+data.id)
        				.next('label').attr('for', 'qTitle'+data.id);
                    $(html).find('input[name="link"]')
                        .attr('id', 'qLink'+data.id)
                        .next('label').attr('for', 'qLink'+data.id);
                    $(html).find('input[name="delay"]')
                        .attr('id', 'qDelay'+data.id)
                        .val(0)
                        .next('label').attr('for', 'qLink'+data.id);
        			$(html).find('input[name="sort"]')
        				.attr('id', 'qOrder'+data.id)
        				.val(data.sort)
        				.next('label').attr('for', 'qOrder'+data.id);

        			$(sessionElement).find('.btn-add-questionnaire-container').before(html);

					setEvents();

        		} else {
        			alert('Error on save session.');
        		}
        	}
        });

	});

	$('.btn-remove-session').off('click').on('click', function(){
		var sessionId = $(this).parents('.session').attr('data-id');

		$.ajax({
        	url 	: 'admin.php',
        	type 	: 'post',
        	dataType: 'json',
        	data 	: {
        		'id'	: sessionId,
        		'action': 'deleteSession'
        	},
        	success : function(data){
        		if (data.result == 'success') {
        			$('.session[data-id="'+sessionId+'"]').remove();
        		} else {
        			alert('Error on save session.');
        		}
        	}
        });

	});

	$('.btn-remove-questionnaire').off('click').on('click', function(){
		var questionnaireId = $(this).parents('.questionnaire').attr('data-id');

		$.ajax({
        	url 	: 'admin.php',
        	type 	: 'post',
        	dataType: 'json',
        	data 	: {
        		'id'	: questionnaireId,
        		'action': 'deleteQuestionnaire'
        	},
        	success : function(data){
        		if (data.result == 'success') {
        			$('.questionnaire[data-id="'+questionnaireId+'"]').remove();
        		} else {
        			alert('Error on save session.');
        		}
        	}
        });


	});

	$('select[name="hour"], select[name="minute"]').off('change').on('change', function(){
		var sessionId = $(this).parents('.session').attr('data-id');
		var sessionElement = $('.session[data-id="'+sessionId+'"]');
		var value = $(this).val();
		var field = $(this).attr('name');

		$.ajax({
        	url 	: 'admin.php',
        	type 	: 'post',
        	dataType: 'json',
        	data 	: {
        		'id'	: sessionId,
        		'field'	: field,
        		'value'	: value,
        		'action': 'updateSession'
        	},
        	success : function(data){
        		if (data.result != 'success') {
        			alert('Error on save session.');
        		}
        	}
        });

		//updateSession
		var hour = $(sessionElement).find('[name="hour"]').val();
		var minute = $(sessionElement).find('[name="minute"]').val();
		var hourMinute =  (hour < 10 ? '0' : '') + hour + ':' + (minute < 10 ? '0' : '') + minute;
		$(sessionElement).find('.hour').html(hourMinute);

	});

	$('.questionnaire input[name="title"], .questionnaire input[name="link"], .questionnaire input[name="delay"], .questionnaire input[name="sort"]').off('change').on('change', function(){

		var questionnaireId = $(this).parents('.questionnaire').attr('data-id');
		var questionnaireElement = $('.questionnaire[data-id="'+questionnaireId+'"]');
		var value = $(this).val();
		var field = $(this).attr('name');

		$.ajax({
        	url 	: 'admin.php',
        	type 	: 'post',
        	dataType: 'json',
        	data 	: {
        		'id'	: questionnaireId,
        		'field'	: field,
        		'value'	: value,
        		'action': 'updateQuestionnaire'
        	},
        	success : function(data){
        		if (data.result != 'success') {
        			alert('Error on save questionnaire.');
        		}
        	}
        });

	});
}