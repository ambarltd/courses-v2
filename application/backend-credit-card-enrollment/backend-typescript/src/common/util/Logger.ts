import winston from 'winston';

const { combine, timestamp, printf } = winston.format;

const customFormat = printf(({ level, message, timestamp, context, ...metadata }) => {
    let msg = `${timestamp} [${level}]`;

    if (context) {
        msg += ` [${context}]`;
    }

    msg += `: ${message}`;

    if (Object.keys(metadata).length > 0) {
        msg += ` ${JSON.stringify(metadata)}`;
    }

    return msg;
});

export const logger = winston.createLogger({
    level: 'debug', // see winston.config.npm.levels.debug
    format: combine(
        timestamp(),
        customFormat
    ),
    transports: [
        new winston.transports.Console({
            format: combine(
                winston.format.colorize(),
                timestamp(),
                customFormat
            )
        })
    ]
});

interface LogContext {
    context?: string;
    [key: string]: any;
}

export const log = {
    debug: (message: string, context?: LogContext) => {
        logger.debug(message, context);
    },

    info: (message: string, context?: LogContext) => {
        logger.info(message, context);
    },

    warn: (message: string, context?: LogContext) => {
        logger.warn(message, context);
    },

    error: (message: string, error?: Error, context?: LogContext) => {
        logger.error(message, {
            ...context,
            error: error ? {
                message: error.message,
                stack: error.stack
            } : undefined
        });
    }
};