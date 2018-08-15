'use strict';


document.addEventListener("DOMContentLoaded", function() {
  var acc = document.getElementsByClassName("js-accordion-button");
  var i;

  for (i = 0; i < acc.length; i++) {
    acc[i].addEventListener("click", function() {
      /* Toggle between adding and removing the "active" class,
      to highlight the button that controls the panel */
      this.classList.toggle("js-accordion-button--active");

      /* Toggle between hiding and showing the active panel */
      var panel = this.nextElementSibling;
      panel.classList.toggle("js-accordion-panel--open");
    });
  }
});