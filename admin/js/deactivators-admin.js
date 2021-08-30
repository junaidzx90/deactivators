jQuery(function( $ ) {
	'use strict';
	// Control Buttons
	var startButton = $('.deactivators-start-btn')
	var nextButton = $('.deactivators-next-btn')

	$('#selall').on('click', function () {
		if ($(this).hasClass('all-active')) {
			$(this).removeClass('all-active')
			$('.activated_plugins').find('li').each(function () {
				$(this).children('button').removeClass('pl-active')
			})
			startButton.prop('disabled', true)
		} else {
			$(this).addClass('all-active')
			$('.activated_plugins').find('li').each(function () {
				$(this).children('button').addClass('pl-active')
			})
			startButton.removeAttr('disabled')
		}
	})

	if (localStorage.getItem('deactivated_plugins')) {
		nextButton.removeAttr('disabled')
		// Show warnings if pending
		$('li#wp-admin-bar-deactivators a.ab-item').css('background-color', '#2e494e')
	}
	
	if (localStorage.getItem('selected_plugins')) {
		startButton.prop('disabled',true)
	}

	// Control menubar
	$(document).on('click','#wp-admin-bar-deactivators a', function (e) {
		e.preventDefault();
		if ($('#deactivators_panel').is(':visible')) {
			$('#deactivators_panel').hide();
			localStorage.removeItem('selected_plugins');
		} else {
			$('#deactivators_panel').show();
		}
	});

	let deactivate_plugins = [] // Deactivate plugins
	let plugins = [] // Selected plugins
	$('.deactivators-start-btn').on('click', function () {
		
		$('.pl-active').each(function () {
			let baseurl = $(this).parent().attr('data-baseurl')
			plugins.push(baseurl);
		})

		// Re assign after clear
		localStorage.removeItem('selected_plugins')
		localStorage.setItem('selected_plugins', plugins);

		// Send request for deactivate plugins
		if (plugins.length > 0) {
			$.ajax({
				type: "post",
				url: deactivators.ajaxurl,
				data: {
					action: "deactivators_deactivate",
					plugin: plugins[0],
					nonce: deactivators.nonce
				},
				beforeSend: () => {
					$('.button').prop('disabled', true)
				},
				dataType: "json",
				success: function (response) {
					if (response.deactivated) {
						if (localStorage.getItem('deactivated_plugins')) {
							deactivate_plugins = localStorage.getItem('deactivated_plugins').split(',')
						}
						deactivate_plugins.push(response.deactivated)
						localStorage.setItem('deactivated_plugins', deactivate_plugins);

						plugins.shift(response.deactivated);
						localStorage.setItem('selected_plugins', plugins);
						location.reload();
					}else{
						$('.button').removeAttr('disabled')
					}
				}
			});
		}
	});

	$('.deactivators-next-btn').on('click', function () {
		let reactivatable = [];
		if (localStorage.getItem('deactivated_plugins')) {
			reactivatable = localStorage.getItem('deactivated_plugins').split(',')
		}
		
		if (localStorage.getItem('selected_plugins')) {
			plugins = localStorage.getItem('selected_plugins').split(',')
		}
		
		// Send request for deactivate plugins
		if (reactivatable.length > 0) {
			$.ajax({
				type: "post",
				url: deactivators.ajaxurl,
				data: {
					action: "deactivators_reactivate",
					reactivatable: reactivatable,
					selected_plugins: plugins[0],
					nonce: deactivators.nonce
				},
				beforeSend: () => {
					$('.button').prop('disabled', true)
				},
				dataType: "json",
				success: function (response) {
					if (response.reactivated) {
						localStorage.removeItem('deactivated_plugins');
						localStorage.setItem('deactivated_plugins', plugins[0]);

						plugins.shift(response.reactivated);
						localStorage.setItem('selected_plugins', plugins);
						location.reload();
					}else{
						$('.button').removeAttr('disabled')
					}
				}
			});
		}
	});

	// Custom checkbox
	$(document).on('click', '.deactivators-plname,.deactivators-checkbox', function (e) {
		if ($(e.target).hasClass('deactivators-plname')) {
			if ($(this).prev('.deactivators-checkbox').hasClass('pl-active')) {
				$(this).prev('.deactivators-checkbox').removeClass('pl-active')
			} else {
				$(this).prev('.deactivators-checkbox').addClass('pl-active')
			}
			return false;
		}

		if ($(e.target).hasClass('deactivators-checkbox')) {
			if ($(this).hasClass('pl-active')) {
				$(this).removeClass('pl-active')
			} else {
				$(this).addClass('pl-active')
			}
			return false;
		}
	});
	
	// Control Start button
	$(document).on('click', '.deactivators-plname,.deactivators-checkbox', function () {
		let selectCounts = 0;
		$('.pl-active').each(function () {
			selectCounts++
		})
		
		if (selectCounts > 0) {
			startButton.removeAttr('disabled')
		} else {
			startButton.prop('disabled', true)
		}
	});

	$('.sidebtn').on('click', function () {
		if ($('.deactivated_plugins').is(':visible')) {
			$('.deactivated_plugins').hide();
			$(this).text('⟩')
		} else {
			$('.deactivated_plugins').show();
			$(this).text('⟨')
		}
	})
	
});
