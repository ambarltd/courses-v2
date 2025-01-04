export class AmbarResponseFactory {
    static retryResponse(exception: Error): string {
        const message = exception.message.replace(/"/g, '\\"');
        return `{"result":{"error":{"policy":"must_retry","class":"${exception.constructor.name}","description":"message:${message}"}}}`;
    }

    static successResponse(): string {
        return '{"result":{"success":{}}}';
    }
}