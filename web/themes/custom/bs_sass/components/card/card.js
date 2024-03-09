(function ($, Drupal) {
  Drupal.behaviors.behaviorName = {
    attach(context, settings) {
      console.log(context);
      const hello = "wolrd";
      const saras = "muchop";

      console.log(hello);
    },
  };
})(jQuery, Drupal);
