import { createHash, randomBytes } from 'crypto';

export class IdGenerator {
    private static readonly ALPHANUMERIC_CHARACTERS = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    private static readonly ID_LENGTH = 56;

    static generateDeterministicId(seed: string): string {
        if (!seed) {
            throw new Error('Input string cannot be null or empty');
        }

        const firstHash = createHash('sha256')
            .update(seed)
            .digest();

        const secondHash = createHash('sha256')
            .update(firstHash)
            .digest();

        const combinedHash = Buffer.concat([firstHash, secondHash]);

        const base64Encoded = combinedHash.toString('base64');
        const cleanId = base64Encoded.replace(/[^A-Za-z0-9]/g, '');

        return cleanId.substring(0, this.ID_LENGTH);
    }

    static generateRandomId(): string {
        const chars = new Array(this.ID_LENGTH);

        for (let i = 0; i < this.ID_LENGTH; i++) {
            const randomByte = randomBytes(1)[0];
            chars[i] = this.ALPHANUMERIC_CHARACTERS.charAt(
                randomByte % this.ALPHANUMERIC_CHARACTERS.length
            );
        }

        return chars.join('');
    }
}