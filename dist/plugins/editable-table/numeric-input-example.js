/* global $ */
/* this is an example for validation and change events */
$.fn.numericInputExample = function () {
	'use strict';
	var element = $(this),
	
		footer = element.find('tfoot tr'),
		dataRows = element.find('tbody tr'),
		initialTotal = function () {
			var column, total;
			for (column = 1; column < footer.children().size(); column++) {
				total = 0;
				dataRows.each(function () {
					var row = $(this);
					total += parseFloat(quitaSeparadorMiles(row.children().eq(column).text()));
				});
				footer.children().eq(column).text(separadorMiles(total));
			};
		};
	element.find('td').on('change', function (evt) {
		
		var cell = $(this),
			column = cell.index(),
			total = 0;
		if (column === 0) {
			return;
		}
		element.find('tbody tr').each(function () {
			var row = $(this);
			total += parseFloat(quitaSeparadorMiles(row.children().eq(column).text()));
		});
		
		$('.alert').hide();
		footer.children().eq(column).text(separadorMiles(total));
		
	}).on('validate', function (evt, value) {
		var cell = $(this),
			column = cell.index();
		if (column === 0) {
			return !!value && value.trim().length > 0;
		} else {
			return !isNaN(parseFloat(value));
		}
	});
	initialTotal();
	return this;
};

