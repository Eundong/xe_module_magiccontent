jQuery(function($) {
	$('label.data_use_check').on('change', 'input[type=checkbox]', function() {
		$label = $(this).parents('label');

		if($(this).attr('checked')) {
			$label.next().show();
		} else {
			$label.next().hide();
			var $target = $label.next();
			$target.find('input[type=text], input[type=hidden]').val('');
			$target.find('input[type=checkbox]:checked').removeAttr('checked');
		}
	});

	$('label.data_custom_check').on('change', 'input[type=checkbox]', function() {
		$label = $(this).parents('label');

		if($(this).attr('checked')) {
			if(!$label.parent().find('input.document_srl').val()) {
				alert('문서를 수동으로 지정하지 않고는 내용 일부 교체는 불가능합니다.');
				$(this).removeAttr('checked');
				return;
			}
			$label.next().show();
		} else {
			$label.next().hide();
			var $target = $label.next();
			$target.find('input[type=text], input[type=hidden]').val('');
			$target.find('input[type=checkbox]:checked').removeAttr('checked');
		}
	});
});
function magicContent() {};
magicContent.prototype.processSetup = function(is_complete, is_auto) {
	$form = jQuery('#setupForm');
	var data = $form.serialize();
	data += '&is_complete=' + is_complete;
	jQuery.ajax({
		type: "POST",
		contentType: "application/json; charset=utf-8",
		dataType: "json",
		url: './index.php',
		error: function (jqXHR, textStatus, errorThrown) {
			alert ('[' + textStatus+ ']' +errorThrown + ' : ' + jqXHR.responseText);
		},
		data: data,
		success: function (json) {
			var scrollTop = 0;
			if(is_auto) {
				scrollTop = jQuery(window).scrollTop()
			} 
			if(is_complete == 0) {
				var url = window.current_url;
				var seed = url.getQuery('seed');
				seed++;
				url = url.setQuery('seed', seed).setQuery('scrollTop', scrollTop);
				window.location.href = url;

			} else {
				setTimeout(function() { window.close(); }, 100);
			}
		}
	});
};

magicContent.prototype.resetTempSetup = function() {
	$form = jQuery('#setupForm');
	var data = { act: 'procMagiccontentResetTempSetup', widget_sequence: $form.find('input[name=widget_sequence]').val() };
	jQuery.ajax({
		type: "POST",
		contentType: "application/json; charset=utf-8",
		dataType: "json",
		url: './index.php',
		error: function (jqXHR, textStatus, errorThrown) {
			alert ('[' + textStatus+ ']' +errorThrown + ' : ' + jqXHR.responseText);
		},
		data: data,
		success: function (json) {
			var scrollTop = 0;
			var url = window.current_url;
			var seed = url.getQuery('seed');
			seed++;
			url = url.setQuery('seed', seed).setQuery('scrollTop', scrollTop);
			window.location.href = url;
		}
	});
};

magicContent.prototype.insertDocument = function(target, document_srl) {
	var $target = jQuery(target);
	var $form = $target.parents('form');
	$form.find('input.document_srl').removeClass('target');
	$target.parent().find('.document_srl').addClass('target');

	var url = window.request_uri.setQuery('act', 'dispMagiccontentInsertDocument').setQuery('widget_sequence', $form.find('input[name=widget_sequence]').val());
	if(document_srl) url = url.setQuery('document_srl', document_srl);

	popopen(url, 'select_document');
}

magicContent.prototype.searchDocument = function(target) {
	var $target = jQuery(target);
	var $form = $target.parents('form');
	$form.find('input.document_srl').removeClass('target');
	$target.parent().find('.document_srl').addClass('target');

	var url = window.request_uri.setQuery('act', 'dispMagiccontentSearchDocument').setQuery('widget_sequence', $form.find('input[name=widget_sequence]').val());
	popopen(url, 'select_document');
}

magicContent.prototype.resetDocument = function(target) {
	var $target = jQuery(target);
	var $form = $target.parents('form');
	$target.parent().find('.document_srl').val('');

	this.processSetup(0);
}

magicContent.prototype.selectDocument = function(target) {
	var $tr = jQuery(target).parents('tr');
	var document_srl = $tr.attr('data-document-srl');
	var title = $tr.attr('data-document-title');

	var $opener = jQuery(opener.document);
	$opener.find('form#setupForm').find('input.document_srl.target').val(document_srl).prev().val(title);
	opener.magiccontent.processSetup(0, 1);
	window.close();
}

var magiccontent = new magicContent();

function completeDocumentInserted(ret) {
	var $opener = jQuery(opener.document);

	$opener.find('form#setupForm').find('input.document_srl.target').val(ret.document_srl).prev().val(ret.title);
	opener.magiccontent.processSetup(0, 1);
	window.close();
}