import { z } from 'zod';

export const requestEnrollmentHttpRequestSchema = z.object({
    productId: z.string(),
    annualIncomeInCents: z.number().min(0, "Annual income cannot be negative").max(1_000_000_000, "Annual income is too high")
});

export type RequestEnrollmentHttpRequest = z.infer<typeof requestEnrollmentHttpRequestSchema>;