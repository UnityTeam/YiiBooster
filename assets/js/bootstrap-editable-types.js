(function ($) {

	var types = $.fn.editable.fieldTypes;
	types.url = $.extend(true, {}, types.text, {
            template: '<div class="input-prepend"><span class="add-on">http://</span><input type="text"></div>'
    });
    types.toggleButton = $.extend(true, {}, types.dflt, {
    	template: '<div ><input type="checkbox" value="1" checked="checked"></div>',
    	inputClass: 'toggle-buttons',
    	renderInput: function() {
            var deferred = $.Deferred();
            this.$input = $(this.settings.field.template);
            this.$input.addClass(this.settings.field.inputclass);
            //console.log(this.$input);
         },
         setInputValue: function() {
            this.$input.find(":input").val(this.value);
            this.$input.toggleButtons();
         },
         getInputValue: function(){
         	return this.$input.find("input").val();
         }


    });



})(jQuery);