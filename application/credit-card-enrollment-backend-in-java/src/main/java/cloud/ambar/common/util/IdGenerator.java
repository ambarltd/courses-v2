package cloud.ambar.common.util;

import java.math.BigInteger;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.security.SecureRandom;

public class IdGenerator {

    private static final String ALPHANUMERIC_CHARACTERS = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
    private static final int ID_LENGTH = 56;

    public static String generateDeterministicId(String seed) {
        if (seed == null || seed.isEmpty()) {
            throw new IllegalArgumentException("Input string cannot be null or empty");
        }

        try {
            // Use SHA-256 to hash the input string
            MessageDigest digest = MessageDigest.getInstance("SHA-256");
            byte[] hashBytes = digest.digest(seed.getBytes());

            // Convert hash bytes to a base-36 string (alphanumeric representation)
            BigInteger hashValue = new BigInteger(1, hashBytes);
            String alphanumericId = hashValue.toString(36);

            // Ensure the result is exactly 56 characters long
            if (alphanumericId.length() > 56) {
                alphanumericId = alphanumericId.substring(0, 56);
            } else if (alphanumericId.length() < 56) {
                // Pad with leading zeros if necessary
                alphanumericId = String.format("%1$" + 56 + "s", alphanumericId).replace(' ', '0');
            }

            return alphanumericId;
        } catch (NoSuchAlgorithmException e) {
            throw new RuntimeException("Error generating deterministic ID: SHA-256 algorithm not found", e);
        }
    }

    public static String generateRandomId() {
        SecureRandom random = new SecureRandom();
        StringBuilder idBuilder = new StringBuilder(ID_LENGTH);

        for (int i = 0; i < ID_LENGTH; i++) {
            int index = random.nextInt(ALPHANUMERIC_CHARACTERS.length());
            idBuilder.append(ALPHANUMERIC_CHARACTERS.charAt(index));
        }

        return idBuilder.toString();
    }
}
