function showMagicContentWidgetSetup(target) {
	var $target = jQuery(target);
	var widget_sequence = $target.attr('data-widget-sequence');
	var url = window.request_uri.setQuery('act', 'dispMagiccontentSetup').setQuery('widget_sequence' ,widget_sequence);
	if(!widget_sequence) {
		alert('위젯 번호가 부여되지 않은 위젯은 데이터를 조정할수 없습니다.\n외부페이지라면 수동으로, 위젯페이지라면 편집화면에서 위젯번호 부여 버튼을 클릭해주세요.');
		return;
	}

	popopen(url, 'magiccontent_setup');
}

jQuery(function($) {
	$('div.widgetOutput').on('mouseover', function() {
		var $widget = $(this);
		if(!$widget.hasClass('widgetOutput')) return;
		setTimeout(function() {
			var $buttons = $widget.find('.widgetButtons');
			console.log($widget.attr('widget_sequence'));
			if(typeof $widget.attr('widget_sequence') != 'undefined') {
				$buttons.find('button.widgetGetSequence').remove();
				return;
			}
			if($buttons.find('button.widgetGetSequence').length > 0) return;
			$buttons.append("<button type='button' class='widgetGetSequence' title='고유번호 부여'>고유번호 부여</button>");
		}, 100);
	});
});

window.doCheckWidget = function(e) {
	if(!e.target) return;
	var obj = e.target;
	var $obj = jQuery(obj);

	selectedWidget = null;

	doHideWidgetSizeSetup();
	// 위젯 설정
	if($obj.hasClass('widgetSetup')) {
		var p_obj = obj.parentNode.parentNode;
		var widget = p_obj.getAttribute("widget");
		if(!widget) return;
		selectedWidget = p_obj;
		if(widget == 'widgetContent') popopen(request_uri+"?module=widget&act=dispWidgetAdminAddContent&module_srl="+zoneModuleSrl+"&document_srl="+p_obj.getAttribute("document_srl"), "addContent");
		else popopen(request_uri+"?module=widget&act=dispWidgetGenerateCodeInPage&selected_widget="+widget+"&widgetstyle="+widgetstyle,'GenerateCodeInPage');
		return;

	// 위젯 스타일
	} else if($obj.hasClass('widgetStyle')) {
		/*jshint -W004*/
		var p_obj = obj.parentNode.parentNode;
		var widget = p_obj.getAttribute("widget");
		var widgetstyle = p_obj.getAttribute("widgetstyle");
		if(!widget) return;
		selectedWidget = p_obj;
		popopen(request_uri+"?module=widget&act=dispWidgetStyleGenerateCodeInPage&selected_widget="+widget+"&widgetstyle="+widgetstyle,'GenerateCodeInPage');
		return;

	// 위젯 복사
	} else if($obj.hasClass('widgetCopy') && jQuery(obj.parentNode.parentNode).hasClass('widgetOutput')) {
		/*jshint -W004*/
		var p_obj = obj.parentNode.parentNode;
		restoreWidgetButtons();

		if(p_obj.getAttribute('widget')=='widgetContent' && p_obj.getAttribute('document_srl') ) {
			var response_tags = ['error','message','document_srl'];
			var params = [];
			params.document_srl =p_obj.getAttribute('document_srl');
			exec_xml('widget','procWidgetCopyDocument', params, completeCopyWidgetContent, response_tags, params, p_obj);
			return;
		} else {
			var dummy = xCreateElement("DIV");
			xInnerHtml(dummy,xInnerHtml(p_obj));

			dummy.widget_sequence = '';
			dummy.className = "widgetOutput";
			for(var i=0;i<p_obj.attributes.length;i++) {
				if(!p_obj.attributes[i].nodeName || !p_obj.attributes[i].nodeValue) continue;
				var name = p_obj.attributes[i].nodeName.toLowerCase();

				var value = p_obj.attributes[i].nodeValue;
				if(!value) continue;

				if(value && typeof(value)=="string") value = value.replace(/\"/ig,'&quot;');

				dummy.setAttribute(name, value);
			}

			if(xIE4Up) dummy.style.cssText = p_obj.style.cssText;
			p_obj.parentNode.insertBefore(dummy, p_obj);
		}
		return;

	// 위젯 사이트/ 여백 조절
	} else if($obj.hasClass('widgetSize') || $obj.hasClass('widgetBoxSize')) {
		var p_obj = obj.parentNode.parentNode;
		var widget = p_obj.getAttribute("widget");
		if(!widget) return;
		selectedWidget = p_obj;
		doShowWidgetSizeSetup(e.pageX, e.pageY, selectedWidget);
		return;

	// 위젯 제거
	} else if($obj.hasClass('widgetRemove') || $obj.hasClass('widgetBoxRemove')) {
		var p_obj = obj.parentNode.parentNode;
		var widget = p_obj.getAttribute("widget");
		if(confirm(confirm_delete_msg)) {
			restoreWidgetButtons();
			p_obj.parentNode.removeChild(p_obj);
		}
		return;
	}  else if($obj.hasClass('widgetGetSequence')) {
		var p_obj = obj.parentNode.parentNode;
		selectedWidget = p_obj;
		if(window.confirm('새 위젯 번호를 발급받으시겠습니까?') == false) return;
		jQuery.ajax({
			type: "POST",
			contentType: "application/json; charset=utf-8",
			dataType: "json",
			url: './index.php',
			error: function (jqXHR, textStatus, errorThrown) {
				alert ('[' + textStatus+ ']' +errorThrown + ' : ' + jqXHR.responseText);
			},
			data: {
				act: 'dispMagiccontentGetNewSequence',
			},
			success: function (json) {
				alert('새 위젯번호를 발급 받았습니다. 페이지를 저장해서 저장해주세요');
				jQuery(selectedWidget).attr('widget_sequence', json.widget_sequence);
				jQuery(selectedWidget).find('button.widgetGetSequence').remove();
			}
		});
		return;
	}

	// 내용 클릭 무효화
	var p_obj = obj;
	while(p_obj) {
		if(jQuery(p_obj).hasClass('widgetOutput')) {
			e.cancelBubble = true;
			e.returnValue = false;
			e.preventDefault();
			e.stopPropagation();
			break;
		}
		p_obj = p_obj.parentNode;
	}
}

window.doCheckWidgetDrag = function(e) {
	if(!e.target) return;
	var obj = e.target;
	var $obj = jQuery(obj);

	if($obj.parents('#pageSizeLayer').size() > 0) return;

	doHideWidgetSizeSetup();

	if($obj.hasClass('widgetSetup') || $obj.hasClass('widgetStyle') || $obj.hasClass('widgetCopy') || $obj.hasClass('widgetBoxCopy') || $obj.hasClass('widgetSize') || $obj.hasClass('widgetBoxSize') || $obj.hasClass('widgetRemove') || $obj.hasClass('widgetBoxRemove') || $obj.hasClass('widgetGetSequence')) return;

	p_obj = obj;
	while(p_obj) {
		var $p_obj = jQuery(p_obj);
		if($p_obj.hasClass('widgetOutput') || $p_obj.hasClass('widgetResize') || $p_obj.hasClass('widgetResizeLeft') || $p_obj.hasClass('widgetBoxResize') || $p_obj.hasClass('widgetBoxResizeLeft')) {
			widgetDragEnable(p_obj, widgetDragStart, widgetDrag, widgetDragEnd);
			widgetMouseDown(e);
			return;
		}
		p_obj = p_obj.parentNode;
	}
};