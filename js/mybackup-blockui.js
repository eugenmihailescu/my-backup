window.BlockUI=(function(c){String.prototype.repeat=function(e){return new Array(e+1).join(this)};function d(e){if(e){e.className+=" blockui"}}function a(e){if(e){e.className=e.className.replace(/ blockui/g,"")}}function b(e,f){if("undefined"==typeof f){f=5}e.setAttribute("value","*".repeat(f))}return{block:d,unblock:a,mask:b}}(this));