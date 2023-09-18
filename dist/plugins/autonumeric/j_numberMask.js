var autoNumericVersion 	= 2;
var optionsName 		= 
[
	"allowDecimalPadding",
	"alwaysAllowDecimalCharacter",
	"caretPositionOnFocus",
	"createLocalList",
	"currencySymbol",
	"currencySymbolPlacement",
	"decimalCharacter",
	"decimalCharacterAlternative",
	"decimalPlaces",
	"decimalPlacesRawValue",
	"decimalPlacesShownOnBlur",
	"decimalPlacesShownOnFocus",
	"defaultValueOverride",
	"digitalGroupSpacing",
	"digitGroupSeparator",
	"divisorWhenUnfocused",
	"emptyInputBehavior",
	"eventBubbles",
	"eventIsCancelable",
	"failOnUnknownOption",
	"formatOnPageLoad",
	"formulaMode",
	"historySize",
	"isCancellable",
	"leadingZero",
	"maximumValue",
	"minimumValue",
	"modifyValueOnWheel",
	"negativeBracketsTypeOnBlur",
	"negativePositiveSignPlacement",
	"negativeSignCharacter",
	"noEventListeners",
	"onInvalidPaste",
	"outputFormat",
	"overrideMinMaxLimits",
	"positiveSignCharacter",
	"rawValueDivisor",
	"readOnly",
	"roundingMethod",
	"saveValueToSessionStorage",
	"selectNumberOnly",
	"selectOnFocus",
	"serializeSpaces",
	"showOnlyNumbersOnFocus",
	"showPositiveSign",
	"showWarnings",
	"styleRules",
	"suffixText",
	"symbolWhenUnfocused",
	"unformatOnHover",
	"unformatOnSubmit",
	"valuesToStrings",
	"watchExternalChanges",
	"wheelOn",
	"wheelStep"
];

function initialize_autonumeric()
{
	$('input.autonumeric').initNumber();
}

(function( $ ){
	$.fn.initNumber= function() 
	{
		return this.each(function() 
		{
			var _id_	= $(this).attr('id');
			var _obj_	= $(this)[0];

			var _data_	= $(this).data();
			var _conf_	= {};

			$.each(_data_,function(att,val)
			{
				if ( typeof optionsName[att]!=='undefined' )
				{
					_conf_[att] 	= val+"";
				}
			});

			if ( autoNumericVersion==2 )
			{
				$('#'+_id_).autoNumeric('init',_conf_);
			}
			else
			{
				window['aN_' + _id_] = new AutoNumeric(_obj_,_conf_);
			}
		});
	};

	$.fn.destroyNumber= function() 
	{
		return this.each(function() 
		{
			var _id_	= $(this).attr('id');
			var _obj_	= $(this)[0];

			if ( autoNumericVersion==2 )
			{
				$('#'+_id_).autoNumeric('destroy');
			}
			else
			{
				window['aN_' + _id_].remove();
			}
		});
	};

	$.fn.updateNumber= function() 
	{
		return this.each(function() 
		{
			var _id_	= $(this).attr('id');
			var _obj_	= $(this)[0];

			if ( autoNumericVersion==2 )
			{
				$('#'+_id_).autoNumeric('update');
			}
			else
			{
				window['aN_' + _id_].update();
			}
		});
	};

	$.fn.setNumber= function(_value) 
	{
		return this.each(function() 
		{
			var _id_ = $(this).attr('id');

			if ( autoNumericVersion==2 )
			{
				$("#"+_id_).autoNumeric('set', _value);
			}
			else
			{
				window['aN_' + _id_].set(_value);
			}
		});
	};

	$.fn.getNumber= function() 
	{
		var _id_ = $(this).attr('id');

		if ( autoNumericVersion==2 )
		{
			var value = $("#"+_id_).autoNumeric('get');
		}
		else
		{
			var value = window['aN_' + _id_].get()
		}

		return value;
	};
})( jQuery );
