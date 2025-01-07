import {z} from 'zod';

export class ValidationError extends Error {
    constructor(
        public readonly errors: z.ZodError
    ) {
        super('Validation failed');
        this.name = 'ValidationError';
    }
}

export function parseWithValidation<T>(data: unknown, schema?: z.ZodType<T>): T {
    if (!schema) {
        throw new Error('Schema must be provided');
    }

    try {
        return schema.parse(data);
    } catch (error) {
        if (error instanceof z.ZodError) {
            throw new ValidationError(error);
        }
        throw error;
    }
}