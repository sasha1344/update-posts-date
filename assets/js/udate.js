(function( $ ) {
	'use strict';

	$(function() {

		/**
		 * Add new row
		 */
		$(document).on( 'click', '.udate-actions .add-rule', function(e) {
			e.preventDefault();

			var uuid = '_' + Math.random().toString(36).substr(2, 9);
			var clonedRow = $('.udate-table .rule-clone-row').clone().removeClass('rule-clone-row').addClass('rule-row');

			clonedRow.html( clonedRow.html().replace( /\[clone\]/g, '[' + uuid + ']' ) );

			$('.udate-table .udate-row:last').after( clonedRow );
			$('.udate-table').trigger('udate-refresh');
		} );

		/**
		 * Remove row
		 */
		$(document).on( 'click', '.udate-delete-icon', function(e) {
			e.preventDefault();

			$(this).closest('tr').remove();
			$('.udate-table').trigger('udate-refresh');
		} );

		/**
		 * Refresh table on actions.
		 */
		$('.udate-table').on( 'udate-refresh', function(e) {
			var rows = $(e.target).find('.rule-row');

			// Set Rows Counter.
			rows.each(function(index, row){
				$(row).find('.col-num .counter').html( index + 1 );
			});

			// Check Rows Count.
			if ( rows.length > 0 ) {
				$('.udate-not-found').hide();
			} else {
				$('.udate-not-found').show();
			}
		});

		/**
		 * Sortable rows
		 */
		$( '.udate-table' ).sortable({
			items: 'tr:not(.rule-clone)',
			handle: 'td.col-num',
			placeholder: 'ui-state-highlight',
			helper: function (e, ui) {
				ui.children().each(function() {
					$(this).width($(this).width());
				});

				return ui;
			},
			start: function(e, ui){
				ui.placeholder.height( ui.item.height() );
			},
			out: function(){
				$('.udate-table').trigger('udate-refresh');
			}
		}).disableSelection();

		/**
		 * Hide error on changes
		 */
		$( '.udate-table' ).on( 'change', '.udate-input input, .udate-input select', function() {
			$(this).closest('.udate-field').find('.udate-error').hide();
		});

	});

})( jQuery );