!function(a){var b=Garnish.Base.extend({tokens:null,routes:null,$container:null,$addRouteBtn:null,sorter:null,init:function(){this.tokens={},this.routes=[],this.$container=a("#routes");for(var b=this.getRoutes(),d=0;d<b.length;d++){var e=new c(b[d]);this.routes.push(e)}this.sorter=new Garnish.DragSort(b,{axis:Garnish.Y_AXIS,onSortChange:a.proxy(this,"updateRouteOrder")}),this.$addRouteBtn=a("#add-route-btn"),this.addListener(this.$addRouteBtn,"click","addRoute")},getRoutes:function(){return this.$container.children()},updateRouteOrder:function(){for(var b=this.getRoutes(),c={},d=0;d<b.length;d++)c["routeIds["+d+"]"]=a(b[d]).attr("data-id");Craft.postActionRequest("routes/updateRouteOrder",c,a.proxy(function(a,b){"success"==b&&(a.success?Craft.cp.displayNotice(Craft.t("New route order saved.")):Craft.cp.displayError(Craft.t("Couldn’t save new route order.")))},this))},addRoute:function(){new d}}),c=Garnish.Base.extend({$container:null,id:null,locale:null,$locale:null,$url:null,$template:null,modal:null,init:function(b){this.$container=a(b),this.id=this.$container.data("id"),this.locale=this.$container.data("locale"),this.$locale=this.$container.find(".locale:first"),this.$url=this.$container.find(".url:first"),this.$template=this.$container.find(".template:first"),this.addListener(this.$container,"click","edit")},edit:function(){this.modal?this.modal.show():this.modal=new d(this)},updateHtmlFromModal:function(){Craft.routes.locales&&this.$locale.text(this.locale?this.locale:Craft.t("Global"));for(var a="",b=0;b<this.modal.urlInput.elements.length;b++){var c=this.modal.urlInput.elements[b];a+=this.modal.urlInput.isText(c)?c.val():c.prop("outerHTML")}this.$url.html(a),this.$template.html(this.modal.$templateInput.val())}}),d=Garnish.Modal.extend({route:null,$heading:null,$urlInput:null,urlElements:null,$templateInput:null,$saveBtn:null,$cancelBtn:null,$spinner:null,$deleteBtn:null,loading:!1,init:function(b){this.route=b;var c="<h4>"+Craft.t("Add a token")+"</h4>";for(var d in Craft.routes.tokens){var e=Craft.routes.tokens[d];c+='<div class="token" data-name="'+d+'" data-value="'+e+'"><span>'+d+"</span></div>"}var f='<form class="modal fitted route-settings" accept-charset="UTF-8"><div class="header"><h1></h1></div><div class="body"><div class="field"><div class="heading"><label for="url">'+Craft.t("If the URI looks like this")+":</label></div>";if(Craft.routes.locales&&(f+='<table class="inputs fullwidth"><tr><td>'),f+='<div id="url" class="text url ltr"></div>',Craft.routes.locales){f+='</td><td class="thin"><div class="select"><select class="locale"><option value="">'+Craft.t("Global")+"</option>";for(var g=0;g<Craft.routes.locales.length;g++){var h=Craft.routes.locales[g];f+='<option value="'+h+'">'+h+"</option>"}f+="</select></div></td></tr></table>"}f+='<div class="url-tokens">'+c+'</div></div><div class="field"><div class="heading"><label for="template">'+Craft.t("Load this template")+':</label></div><input id="template" type="text" class="text fullwidth template ltr"></div></div><div class="footer"><div class="buttons right last"><input type="button" class="btn cancel" value="'+Craft.t("Cancel")+'"><input type="submit" class="btn submit" value="'+Craft.t("Save")+'"> <div class="spinner" style="display: none;"></div></div><a class="delete">'+Craft.t("Delete")+"</a></div></form>";var i=a(f).appendTo(Garnish.$bod);if(this.$heading=i.find("h1:first"),this.$localeInput=i.find(".locale:first"),this.$urlInput=i.find(".url:first"),this.$templateInput=i.find(".template:first"),this.$saveBtn=i.find(".submit:first"),this.$cancelBtn=i.find(".cancel:first"),this.$spinner=i.find(".spinner:first"),this.$deleteBtn=i.find(".delete:first"),this.route||this.$deleteBtn.hide(),this.urlInput=new Garnish.MixedInput(this.$urlInput,{dir:"ltr"}),this.$heading.html(this.route?Craft.t("Edit Route"):Craft.t("Create a new route")),this.route){this.$localeInput.val(this.route.locale);for(var j=this.route.$url.prop("childNodes"),g=0;g<j.length;g++){var k=j[g];if(Garnish.isTextNode(k)){var l=this.urlInput.addTextElement();l.setVal(k.nodeValue)}else this.addUrlVar(k)}setTimeout(a.proxy(function(){var a=this.urlInput.elements[0];this.urlInput.setFocus(a),this.urlInput.setCarotPos(a,0)},this),1);var m=this.route.$template.text();this.$templateInput.val(m)}else setTimeout(a.proxy(function(){this.$urlInput.focus()},this),100);this.base(i);var n=this.$container.find(".url-tokens").children("div");this.addListener(n,"mousedown",function(a){this.addUrlVar(a.currentTarget)}),this.addListener(this.$container,"submit","saveRoute"),this.addListener(this.$cancelBtn,"click","cancel"),this.addListener(this.$deleteBtn,"click","deleteRoute")},addUrlVar:function(b){var c=a(b).clone().attr("tabindex","0");this.urlInput.addElement(c),this.addListener(c,"keydown",function(b){switch(b.keyCode){case Garnish.LEFT_KEY:setTimeout(a.proxy(function(){this.urlInput.focusPreviousElement(c)},this),1);break;case Garnish.RIGHT_KEY:setTimeout(a.proxy(function(){this.urlInput.focusNextElement(c)},this),1);break;case Garnish.DELETE_KEY:setTimeout(a.proxy(function(){this.urlInput.removeElement(c)},this),1),b.preventDefault()}})},show:function(){this.route&&(this.$heading.html(Craft.t("Edit Route")),this.$deleteBtn.show()),this.base()},saveRoute:function(b){if(b.preventDefault(),!this.loading){var d={locale:this.$localeInput.val()};this.route&&(d.routeId=this.route.id);for(var e=0;e<this.urlInput.elements.length;e++){var f=this.urlInput.elements[e];this.urlInput.isText(f)?d["url["+e+"]"]=f.val():(d["url["+e+"][0]"]=f.attr("data-name"),d["url["+e+"][1]"]=f.attr("data-value"))}d.template=this.$templateInput.val(),this.loading=!0,this.$saveBtn.addClass("active"),this.$spinner.show(),Craft.postActionRequest("routes/saveRoute",d,a.proxy(function(b,d){if(this.$saveBtn.removeClass("active"),this.$spinner.hide(),this.loading=!1,"success"==d)if(b.success){if(!this.route){var e='<div class="pane route" data-id="'+b.routeId+'"'+(b.locale?' data-locale="'+b.locale+'"':"")+'><div class="url-container">';Craft.routes.locales&&(e+='<span class="locale"></span>'),e+='<span class="url" dir="ltr"></span></div><div class="template" dir="ltr"></div></div>';var f=a(e);f.appendTo("#routes"),this.route=new c(f),this.route.modal=this,Craft.routes.sorter.addItems(f),1==Craft.routes.sorter.$items.length&&a("#noroutes").addClass("hidden")}this.route.locale=b.locale,this.route.updateHtmlFromModal(),this.hide(),Craft.cp.displayNotice(Craft.t("Route saved."))}else Craft.cp.displayError(Craft.t("Couldn’t save route."))},this))}},cancel:function(){this.hide(),this.route&&(this.route.modal=null)},deleteRoute:function(){confirm(Craft.t("Are you sure you want to delete this route?"))&&(Craft.postActionRequest("routes/deleteRoute",{routeId:this.route.id},function(a,b){"success"==b&&Craft.cp.displayNotice(Craft.t("Route deleted."))}),Craft.routes.sorter.removeItems(this.route.$container),this.route.$container.remove(),this.hide(),0==Craft.routes.sorter.$items.length&&a("#noroutes").removeClass("hidden"))}});Craft.routes=new b}(jQuery);
//# sourceMappingURL=routes.js.map