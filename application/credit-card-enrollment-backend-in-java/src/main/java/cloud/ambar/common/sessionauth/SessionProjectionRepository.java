package cloud.ambar.common.sessionauth;

import org.springframework.data.mongodb.repository.MongoRepository;

import java.util.Optional;

public interface SessionProjectionRepository extends MongoRepository<Session, String> {
    Optional<Session> getBySessionToken(String sessionToken);
}