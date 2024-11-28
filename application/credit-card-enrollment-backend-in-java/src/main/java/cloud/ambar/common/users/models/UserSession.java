package cloud.ambar.common.users.models;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import jakarta.persistence.Id;
import lombok.Data;
import lombok.NoArgsConstructor;
import org.springframework.data.mongodb.core.index.Indexed;
import org.springframework.data.mongodb.core.mapping.Document;

@Data
@NoArgsConstructor
@Document(collection = "AuthenticationForAllContexts_Session_Session")
@JsonIgnoreProperties(ignoreUnknown = true)
public class UserSession {
    @Id
    private String userId;
    @Indexed // indicate there is an index on this attribute
    private String sessionToken;
}
