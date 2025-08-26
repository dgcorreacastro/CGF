require('dotenv').config();
const mysql = require('mysql2/promise');

const createConnection = async () => {
    try {
        const dbConnection = await mysql.createConnection({
            host: process.env.DB_HOST,
            user: process.env.DB_USER,
            password: process.env.DB_PASSWORD,
            database: process.env.ENV === '1' ? process.env.DB_NAME : process.env.DB_NAME_DEV
        });

        console.log('Conexão com o banco de dados Local estabelecida.');
        
        return dbConnection;
    } catch (error) {
        console.error('Erro ao conectar ao banco de dados Local:', error.message);
        throw error;
    }
};

module.exports = { createConnection };