import { z } from 'zod';

export function typeSafeCoercion<T>(data: unknown): T {
    const schema = z.custom<T>().transform((val) => val as T);
    return schema.parse(data);
}