<?
header('Content-Type: text/x-component');
?>
<public:attach event="ondocumentready" onevent="CSSHover()" />
<script>
// <![CDATA[
/**
 *	Whatever:hover - V3.00.081222
 *	------------------------------------------------------------
 *	Author  - Peter Nederlof, http://www.xs4all.nl/~peterned
 *	License - http://creativecommons.org/licenses/LGPL/2.1
 *
 *	Whatever:hover is free software; you can redistribute it and/or
 *	modify it under the terms of the GNU Lesser General Public
 *	License as published by the Free Software Foundation; either
 *	version 2.1 of the License, or (at your option) any later version.
 *
 *	Whatever:hover is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 *	Lesser General Public License for more details.
 *
 *	howto: body { behavior:url("csshover3.htc"); }
 *	------------------------------------------------------------
 */

window.CSSHover = (function(){

	// regular expressions, used and explained later on.
	var REG_INTERACTIVE = /(^|\s)((([^a]([^ ]+)?)|(a([^#.][^ ]+)+)):(hover|active|focus))/i,
		REG_AFFECTED = /(.*?)\:(hover|active|focus)/i,
		REG_PSEUDO = /[^:]+:([a-z-]+).*/i,
		REG_SELECT = /(\.([a-z0-9_-]+):[a-z]+)|(:[a-z]+)/gi,
		REG_CLASS = /\.([a-z0-9_-]*on(hover|active|focus))/i,
		REG_MSIE = /msie (5|6|7)/i,
		REG_COMPAT = /backcompat/i;

	// css prefix, a leading dash would be nice (spec), but IE6 doesn't like that.
	var CSSHOVER_PREFIX = 'csh-';
	
	/**
	 *	Local CSSHover object
	 *	--------------------------
	 */
	
	var CSSHover = {
		
		// array of CSSHoverElements, used to unload created events
		elements: [], 
		
		// buffer used for checking on duplicate expressions
		callbacks: {}, 
		
		// init, called once ondomcontentready via the exposed window.CSSHover function
		init:function() {
			// don't run in IE8 standards; expressions don't work in standards mode anyway, 
			// and the stuff we're trying to fix should already work properly
			if(!REG_MSIE.test(navigator.userAgent) && !REG_COMPAT.test(window.document.compatMode)) return;

			// start parsing the existing stylesheets
			var sheets = window.document.styleSheets, l = sheets.length;
			for(var i=0; i<l; i++) {
				this.parseStylesheet(sheets[i]);
			}
		},

		// called from init, parses individual stylesheets
		parseStylesheet:function(sheet) {
			// check sheet imports and parse those recursively
			if(sheet.imports) {
				try {
					var imports = sheet.imports, l = imports.length;
					for(var i=0; i<l; i++) {
						this.parseStylesheet(sheet.imports[i]);
					}
				} catch(securityException){
					// trycatch for various possible errors,
					// todo; might need to be placed inside the for loop, since an error
					// on an import stops following imports from being processed.
				}
			}
			
			// interate the sheet's rules and send them to the parser
			try {
				var rules = sheet.rules, l = rules.length;
				for(var j=0; j<l; j++) {
					this.parseCSSRule(rules[j], sheet);
				}
			} catch(securityException){
				// trycatch for various errors, most likely accessing the sheet's rules,
				// don't see how individual rules would throw errors, but you never know.
			}
		},

		// magic starts here ...
		parseCSSRule:function(rule, sheet) {
			
			// The sheet is used to insert new rules into, this must be the same sheet the rule 
			// came from, to ensure that relative paths keep pointing to the right location.

			// only parse a rule if it contains an interactive pseudo.
			var select = rule.selectorText;
			if(REG_INTERACTIVE.test(select)) {
				var style = rule.style.cssText,
					
					// affected elements are found by truncating the selector after the interactive pseudo,
					// eg: "div li:hover" >>  "div li"
					affected = REG_AFFECTED.exec(select)[1],
					
					// that pseudo is needed for a classname, and defines the type of interaction (focus, hover, active)
					// eg: "li:hover" >> "onhover"
					pseudo = select.replace(REG_PSEUDO, 'on$1'),
					
					// the new selector is going to use that classname in a new css rule,
					// since IE6 doesn't support multiple classnames, this is merged into one classname
					// eg: "li:hover" >> "li.onhover",  "li.folder:hover" >> "li.folderonhover"
					newSelect = select.replace(REG_SELECT, '.$2' + pseudo),
					
					// the classname is needed for the events that are going to be set on affected nodes
					// eg: "li.folder:hover" >> "folderonhover"
					className = REG_CLASS.exec(newSelect)[1];

				// no need to set the same callback more than once when the same selector uses the same classname
				var hash = affected + className;
				if(!this.callbacks[hash]) {

					// affected elements are given an expression under a fake css property, the classname is used
					// because a unique name (eg "behavior:") would be overruled (in IE6, not 7) by a following rule 
					// selecting the same element. The expression does a callback to CSSHover.patch, rerouted via the
					// exposed window.CSSHover function. 

					// because the expression is added to the stylesheet, and styles are always applied to html that is
					// dynamically added to the dom, the expression will also trigger for those new elements (provided
					// they are selected by the affected selector). 

					sheet.addRule(affected, CSSHOVER_PREFIX + className + ':expression(CSSHover(this, "'+pseudo+'", "'+className+'"))');
					
					// hash it, so an identical selector/class combo does not duplicate the expression
					this.callbacks[hash] = true;
				}
				
				// duplicate expressions need not be set, but the style could differ
				sheet.addRule(newSelect, style);
			}
		},

		// called via the expression, patches individual nodes
		patch:function(node, type, className) {
			
			// the patch's type is returned to the expression. That way the expression property
			// can be found and removed, to stop it from calling patch over and over. 
			// The if will fail the first time, since the expression has not yet received a value.
			var property = CSSHOVER_PREFIX + className;
			if(node.style[property]) {
				node.style[property] = null;
			}

			// just to make sure, also keep track of patched classnames locally on the node
			if(!node.csshover) node.csshover = [];

			// and check for it to prevent duplicate events with the same classname from being set
			if(!node.csshover[className]) {
				node.csshover[className] = true;

				// create an instance for the given type and class
				var element = new CSSHoverElement(node, type, className);
				
				// and store that instance for unloading later on
				this.elements.push(element);
			}

			// returns a dummy value to the expression
			return type;
		},

		// unload stuff onbeforeunload
		unload:function() {
			try {
				
				// remove events
				var l = this.elements.length;
				for(var i=0; i<l; i++) {
					this.elements[i].unload();
				}

				// and set properties to null 
				this.elements = [];
				this.callbacks = {};

			} catch (e) {
			}
		}
	};

	// add the unload to the onbeforeunload event
	window.attachEvent('onbeforeunload', function(){
		CSSHover.unload();
	});

	/**
	 *	CSSHoverElement
	 *	--------------------------
	 */

	// the event types associated with the interactive pseudos
	var CSSEvents = {
		onhover:  { activator: 'onmouseenter', deactivator: 'onmouseleave' },
		onactive: { activator: 'onmousedown',  deactivator: 'onmouseup' },
		onfocus:  { activator: 'onfocus',      deactivator: 'onblur' }
	};
	
	// CSSHoverElement constructor, called via CSSHover.patch
	function CSSHoverElement(node, type, className) {

		// the CSSHoverElement patches individual nodes by manually applying the events that should 
		// have fired by the css pseudoclasses, eg mouseenter and mouseleave for :hover. 

		this.node = node;
		this.type = type;
		var replacer = new RegExp('(^|\\s)'+className+'(\\s|$)', 'g');

		// store event handlers for removal onunload
		this.activator =   function(){ node.className += ' ' + className; };
		this.deactivator = function(){ node.className = node.className.replace(replacer, ' '); };
		
		// add the events
		node.attachEvent(CSSEvents[type].activator, this.activator);
		node.attachEvent(CSSEvents[type].deactivator, this.deactivator);
	}
	
	CSSHoverElement.prototype = {
		// onbeforeunload, called via CSSHover.unload
		unload:function() {

			// remove events 
			this.node.detachEvent(CSSEvents[this.type].activator, this.activator);
			this.node.detachEvent(CSSEvents[this.type].deactivator, this.deactivator);

			// and set properties to null 
			this.activator = null;
			this.deactivator = null;
			this.node = null;
			this.type = null;
		}
	};

	/**
	 *	Public hook
	 *	--------------------------
	 */
	
	return function(node, type, className) {
		if(node) {
			// called via the css expression; patches individual nodes
			return CSSHover.patch(node, type, className);
		} else {
			// called ondomcontentready via the public:attach node
			CSSHover.init();
		}
	};

})();

// ]]>
</script>