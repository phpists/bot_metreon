$(document).ready(function(){
	var indexTab = 0;
	
	$('body').on('update', function(){
		console.log('update');
		
		setTimeout(function(){
			console.log('start update');
			
			indexTab = 0;
			
			$('body').trigger('errors');
			
			$('.has-many-tabs-form, .has-many-tabs-forms > .fields-group').each(function(i, tab){
				$(tab).attr('data-new', '0');
				
				tabProccessing(tab);
			});
		}, 1000);
	});
	
	if($('#has-many-tabs').length < 1){
		//return false;
	};
	
	// tabs
	
	$(document).off('click', '#has-many-tabs > .nav i.close-tab').on('click', '#has-many-tabs > .nav i.close-tab', function(){
		var $navTab = $(this).siblings('a');
		var $pane = $($navTab.attr('href'));
		
		if( $pane.hasClass('new') ){
			$pane.remove();
		}else{
			$pane.removeClass('active').find('.fom-removed').val(1);
		};
		
		if($navTab.closest('li').hasClass('active')){
			$navTab.closest('li').remove();
			
			$('#has-many-tabs > .nav > li:nth-child(1) > a').tab('show');
		}else{
			$navTab.closest('li').remove();
		}
	});
	
	$(document).off('click', '#has-many-tabs > .header .add').on('click', '#has-many-tabs > .header .add', function(){
		indexTab++;
		
		var navTabHtml = $('#has-many-tabs > template.nav-tab-tpl').html().replace(/__LA_KEY__/g, indexTab);
		var paneHtml = $('#has-many-tabs > template.pane-tpl').html().replace(/__LA_KEY__/g, indexTab);
		
		$('#has-many-tabs > .nav').append(navTabHtml);
		$('#has-many-tabs > .tab-content').append(paneHtml);
		$('#has-many-tabs > .nav > li:last-child a').tab('show');
		
		$('.tabs.sort').inputmask({"alias":"decimal","rightAlign":true});
	});
	
	$('body').on('errors', function(){
		$('.has-error').parent('.tab-pane').each(function(){
			var tabId = '#'+$(this).attr('id');
			
			$('li a[href="'+tabId+'"] i').removeClass('hide');
		});
		
		var first = $('.has-error:first').parent().attr('id');
		
		$('li a[href="#'+first+'"]').tab('show');
	});
	
	if($('#has-many-tabs .has-error').length){
		$('body').trigger('errors');
	};
	
	// items
    
    $('.has-many-tabs-form, .has-many-tabs-forms > .fields-group').each(function(i, tab){
		$(tab).attr('data-new', '0');
		
		tabProccessing(tab);
	});
	
	$(document).on('click', '.has-many-tabs > .header > .col-md-8 button.add', function(e){
		setTimeout(function(){
			$('.has-many-tabs-form, .has-many-tabs-forms > .fields-group').each(function(i, tab){
				$(tab).attr('data-new', '1');
				
				tabProccessing(tab);
			});
		}, 200);
	});
	
	//return false;
    
	$(document).on('click', '.has-many-extra-wrap .add', function(e){
		var current = $(this),
			parent	= current.parents('.has-many-extra-wrap'),
			group	= parent.parents('.fields-group'),
			index	= parent.attr('data-c'),
			n		= parent.attr('data-n');
		
		var isNew = group.attr('data-new') == '1';
		
		var tpl = parent.children('template.extra-tpl');
		
		index++;
		parent.attr('data-c', index);
		
		if(!isNew){
			var template = tpl.html().replace(/new___LA_KEY__/g, index).replace(/extra\[/g, 'tabs['+n+'][extra][').replace(/\[new_/g, '[');
		}else{
			var n2 = group.attr('id').replace('tabs_', '').replace('new_', '');
			
			console.log('index:', index);
			
			var template = tpl.html().replace(/\[new_\d+\]/g, '[new_'+index+']').replace(/extra\[/g, 'tabs[new_'+n2+'][extra][');
		};
		
		//.replace(/_remove_/g, 'remove');
		
		template = $(template);
		template.addClass('column');
		
		template.find('input,textarea').removeAttr("id");
		
		var sort = template.find('input.extra.sort');
		
		if(sort.length){
			var last = parent.find('.has-many-extra-forms > tr:last-child input.extra.sort');
			
			if(last.length){
				last = parseInt(last.val());
				
				if(!isNaN(last)){
					last++;
					
					sort.val(last);
				}
			}else{
				sort.val(1);
			};
			
			sort.css('text-align', 'left');
		};
		
		parent.find('.has-many-extra-forms').append(template);
		
		var highlight = parent.find('.extra.highlight.la_checkbox');
		
		if(highlight.length){
			highlight.bootstrapSwitch({
				size			: 'small',
				onText			: 'ON',
				offText			: 'OFF',
				onColor			: 'primary',
				offColor		: 'default',
				onSwitchChange	: function(event, state) {
					$(event.target).closest('.bootstrap-switch').next().val(state ? 'on' : 'off').change();
				}
			});
		};
		
		return false;
	});
	
	$(document).on('click', '.has-many-extra-wrap .remove', function(e){
		var current = $(this),
			parent	= current.parents('tr.fields-group'),
			wrap	= parent.parents('.has-many-extra-wrap');
		
		parent.removeClass('column');
		parent.hide();
		parent.find('.fom-removed').val(1);
		
		//var index = parent.attr('data-c');
		//index--;
		//parent.attr('data-c', index);
		
		return false;
	});
});

function tabProccessing(tab, n){
	var tab = $(tab);
	
	if(tab.attr('data-proc') == '1'){
		return true;
	};
	
	var id = tab.attr('id');
	var n = tab.attr('id').replace('tabs_', '').replace('new_', '');
	
	tab.attr('data-proc', '1');
	
	var extra = $('#'+id+' > .row > .col-sm-8 > div');
	
	if(extra.length){
		extra.attr('id', 'has-many-extra-'+n);
		extra.attr('data-n', n);
		
		var groups = extra.find('table .has-many-extra-forms > .fields-group');
		
		extra.attr('data-c', groups.length);
		
		if(groups.length){
			var index = 1;
			groups.each(function(j, g){
				g = $(g);
				
				g.addClass('column');
				
				var inputs = g.find('input,textarea');
				
				inputs.each(function(k, input){
					input = $(input);
					
					var name = input.attr('name');
					
					if(name !== undefined){
						name = name.replace(/new___LA_KEY__/g, index).replace(/extra\[/g, 'tabs['+n+'][extra][');
						
						input.attr('name', name);
						
						input.removeAttr("id");
						
						input.css('text-align', 'left');
					}
				});
				
				index++;
			});
		};
		
		extra.addClass('has-many-extra-wrap');
	};
	
	//console.log(extra);
};
