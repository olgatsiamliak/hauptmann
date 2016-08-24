(function($) {
    $.fn.validateIt = function(options) {
        
        options = $.extend({
            validClass : "valid",
            wrongClass : "wrong",
            onsubmit : function(){}
        }, options);
        
        
        var setValidate = function() {
            $(this).find("input[minlength]").each(function(){
                $(this).change(function(){
                    checkMinLenght($(this));
                });
            });
            
            $(this).find("input[phone]").each(function(){
                $(this).change(function(){
                    checkPhone($(this));
                });
            });
            
            $(this).find("input[email]").each(function(){
                $(this).change(function(){
                    checkEmail($(this));
                });
            });
        }
        
        this.each(setValidate);
        
        this.each(function(){
            $(this).on('submit', function(e) {
                e.preventDefault();
                var valid = true;
                $(this).find("input[minlength]").each(function(){
                    if(checkMinLenght($(this))){
                        valid = false;
                    };
                });
                
                $(this).find("input[phone]").each(function(){
                    if(checkPhone($(this))){
                        valid = false;
                    };
                });
                
                $(this).find("input[email]").each(function(){
                    if(checkEmail($(this))){
                        valid = false;
                    };
                });
                
                if(valid){
                    var thisForm = $(this);
                    $.ajax($(this).attr('action'), {
                        data: $(this).serializeArray(),
                        type: $(this).attr('method'),
                    }).done(function(res) {
                        thisForm.trigger('reset');
                        options.onsubmit();
                    });
                }
            });
        });

        function checkMinLenght(el) {
            if (el[0].value.length < parseInt(el.attr("minlength"))) {
                el.removeClass(options.validClass).addClass(options.wrongClass);
                el[0].setCustomValidity("Не менее "+el.attr("minLength")+" символов");
                return true;
            }
            else {
                el[0].setCustomValidity("");
                el.removeClass(options.wrongClass).addClass(options.validClass);
            }
        }
        
        function checkPhone(el) {
            if(el.val().length > 5) {
                var pattern = /^[0-9-+()s]/;
                if(pattern.test(el.val())){
                	el.removeClass(options.wrongClass).addClass(options.validClass);
                	el[0].setCustomValidity("");
                } else {
                	el.removeClass(options.validClass).addClass(options.wrongClass);
                	el[0].setCustomValidity("Это не номер телефона");
                	return true;
                }
            } else {
                el.removeClass(options.validClass).addClass(options.wrongClass);
                el[0].setCustomValidity("Не менее 6 символов");
                return true;
            }
        }
        
        function checkEmail(el) {
        	if(el.val().length != '') {
        		var pattern = /^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/;
        		if(pattern.test(el.val())){
        			el.removeClass(options.wrongClass).addClass(options.validClass);
        		} else {
        			el.removeClass(options.validClass).addClass(options.wrongClass);
        			return true;
        		}
        	} else {
                el.removeClass(options.validClass).addClass(options.wrongClass);
                return true;
        	}
        }

    };
})(jQuery);
