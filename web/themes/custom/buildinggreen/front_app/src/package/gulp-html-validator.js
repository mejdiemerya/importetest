const es = require('event-stream');
const path = require('path');
const chalk = require('chalk');
const htmlv = require('html-validator');

module.exports = function (options) {

    var index = 1;

    options = Object.assign({}, {
        extract: true
    }, options);

    var log = function (err) {
        if (err.type === 'error') {
            console.log(chalk.whiteBright.bgRed(index++ + ' - Error ') + chalk.red(` line ${err.lastLine}, col ${err.lastColumn}, ${err.message}`));
        } else {
            if (err.subType === 'warning') {
                console.log(chalk.whiteBright.bgYellow(index++ + ' - Warning ') + chalk.yellow(` line ${err.lastLine}, col ${err.lastColumn}, ${err.message}`));
            } else {
                console.log(chalk.whiteBright.bgBlue(index++ + ' - Info ') + chalk.blue(` line ${err.lastLine}, col ${err.lastColumn}, ${err.message}`));
            }
        }
        if (options.extract) {
            console.log(chalk.bgRgb(255, 255, 128).black(err.extract));
            console.log('\n');
        }
    };

    function modifyContents(file, cb) {
        if (file.isNull()) return cb(null, file); // pass along
        if (file.isStream()) return cb(new Error('htmlv: Streaming not supported')); // pass error if streaming is not supported

        options.data = file.contents.toString('utf8');

        htmlv(options, function (err, data) {


            console.log('\n' + file.path);

            //console.log(`  line ${err.line}, col ${err.character}, code ${err.code}, ${err.reason}`);
            if (err) return cb(err);

            if (options.format === 'json') {
                data.messages.forEach(function (err) {
                    log(err);
                });
                file.contents = new Buffer(JSON.stringify(data));
            } else {
                console.log(data);
                file.contents = new Buffer(String(data));
            }

            return cb(null, file);
        });
    }


    // Return a stream
    return es.map(modifyContents);
};