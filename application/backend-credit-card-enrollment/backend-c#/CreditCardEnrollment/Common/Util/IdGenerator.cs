using System.Security.Cryptography;
using System.Text;

namespace CreditCardEnrollment.Common.Util;

public static class IdGenerator {
    private const string AlphanumericCharacters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
    private const int IdLength = 56;

    public static string GenerateDeterministicId(string seed) {
        if (string.IsNullOrEmpty(seed)) {
            throw new ArgumentException("Input string cannot be null or empty");
        }

        var sha256 = SHA256.Create();
        var firstHash = sha256.ComputeHash(Encoding.UTF8.GetBytes(seed));
        var secondHash = sha256.ComputeHash(firstHash);
        var combinedHash = new byte[firstHash.Length + secondHash.Length];

        Buffer.BlockCopy(firstHash, 0, combinedHash, 0, firstHash.Length);
        Buffer.BlockCopy(secondHash, 0, combinedHash, firstHash.Length, secondHash.Length);

        var base64Encoded = Convert.ToBase64String(combinedHash);
        var cleanId = string.Concat(base64Encoded.Where(char.IsLetterOrDigit));

        return cleanId[..IdLength];
    }

    public static string GenerateRandomId() {
        using var rng = RandomNumberGenerator.Create();
        var chars = new char[IdLength];

        for (var i = 0; i < IdLength; i++) {
            var randomByte = new byte[1];
            rng.GetBytes(randomByte);
            chars[i] = AlphanumericCharacters[randomByte[0] % AlphanumericCharacters.Length];
        }

        return new string(chars);
    }
}