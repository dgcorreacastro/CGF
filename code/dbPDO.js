require('dotenv').config();
const sql = require('mssql');

const createPdoConnection = async () => {
    try {
        const config = {
            user: process.env.DB_PDO_USER,
            password: process.env.DB_PDO_PASSWORD,
            server: process.env.DB_PDO_HOST,
            port: parseInt(process.env.DB_PDO_PORT),
            database: process.env.DB_PDO_NAME,
            options: {
                encrypt: false
            }
        };
        const pool = new sql.ConnectionPool(config);
        const dbPdoConnection = await pool.connect();
        console.log('Conexão com o banco de dados PDO estabelecida.');
        return dbPdoConnection;
    } catch (error) {
        console.error('Erro ao conectar ao banco de dados PDO:', error.message);
        throw error;
    }
};

module.exports = { createPdoConnection };


module.exports = { createPdoConnection };