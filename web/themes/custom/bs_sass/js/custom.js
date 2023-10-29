/**
 * @file
 * Global utilities.
 *
 */
(function init($, Drupal) {
  Drupal.behaviors.bs_sass = {
    attach(context, settings) {
      // Custom code here
      const hello = "world";

      const myFunc = () => {
        console.log("my FUNC");
      };
      myFunc();
    },
  };
})(jQuery, Drupal);
