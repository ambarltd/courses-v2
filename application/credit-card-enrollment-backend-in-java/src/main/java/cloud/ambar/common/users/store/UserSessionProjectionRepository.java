package cloud.ambar.common.users.store;

import cloud.ambar.common.users.models.UserSession;
import org.springframework.data.mongodb.repository.MongoRepository;

import java.util.Optional;

public interface UserSessionProjectionRepository extends MongoRepository<UserSession, String> {
    Optional<UserSession> getBySessionToken(String sessionToken);
}