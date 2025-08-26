// momentConfig.js
const moment = require('moment-timezone');
const timezone = 'America/Sao_Paulo';
moment.tz.setDefault(timezone);

module.exports = moment;