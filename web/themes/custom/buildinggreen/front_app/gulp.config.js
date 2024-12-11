module.exports = function () {
  const config = {
    app: "rapportcongres",
    src: "./src/",
    dist: "./dist/",
    deploySrc: "./dist/assets/*/**",
    sass: {
      src: "src/scss/composant-import/",
      composant: [
        "default-composant",
        "hero-composant",
        "forum-composant",
        "blog-composant",
        "basic-page-composant",
      ],
      quality: ["src/scss/**/*.scss"],
      dest: "./dist/assets/composant/",
      output: "styles.css",
      watch: ["./src/scss/**/*.scss"],
    },
    js: {
      composant: ["default-composant"],
      src: "./src/js/composant/",
      quality: ["./src/js/**/*.js"],
      dest: "./dist/assets/composant/",
      output: "scripts.js",
      watch: ["./src/js/**/*.js"],
    },
    img: {
      src: "./src/images/**/*",
      dest: "./dist/assets/images/",
      watch: ["./src/images/**/*"],
    },
    fonts: {
      src: "./src/fonts/**/*",
      dest: "./dist/assets/fonts/",
      watch: ["./src/fonts/**/*"],
    },
  };
  return config;
};
