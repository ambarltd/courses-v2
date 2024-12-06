package cloud.ambar.common.util;

import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.security.SecureRandom;
import java.util.Base64;

public class IdGenerator {

    private static final String ALPHANUMERIC_CHARACTERS = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
    private static final int ID_LENGTH = 56;

    public static String generateDeterministicId(String seed) {
        if (seed == null || seed.isEmpty()) {
            throw new IllegalArgumentException("Input string cannot be null or empty");
        }

        try {
            MessageDigest digest = MessageDigest.getInstance("SHA-256");
            byte[] firstHash = digest.digest(seed.getBytes());
            byte[] secondHash = digest.digest(firstHash);
            byte[] combinedHash = new byte[firstHash.length + secondHash.length];

            System.arraycopy(firstHash, 0, combinedHash, 0, firstHash.length);
            System.arraycopy(secondHash, 0, combinedHash, firstHash.length, secondHash.length);

            String base64Encoded = Base64.getEncoder().encodeToString(combinedHash);
            String cleanId = base64Encoded.replaceAll("[^A-Za-z0-9]", "0");

            return cleanId.substring(0, ID_LENGTH);

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