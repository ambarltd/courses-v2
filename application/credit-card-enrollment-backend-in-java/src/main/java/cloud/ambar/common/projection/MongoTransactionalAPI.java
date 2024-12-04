package cloud.ambar.common.projection;

import com.mongodb.ReadConcern;
import com.mongodb.ReadPreference;
import com.mongodb.TransactionOptions;
import com.mongodb.WriteConcern;
import com.mongodb.client.ClientSession;
import lombok.RequiredArgsConstructor;
import org.springframework.data.mongodb.core.MongoTemplate;

@RequiredArgsConstructor
public class MongoTransactionalAPI {
    private final MongoTemplate mongoTemplate;
    private final ClientSession session;

    public void startTransaction() {
        session.startTransaction(
                TransactionOptions.builder()
                        .writeConcern(WriteConcern.MAJORITY)
                        .readConcern(ReadConcern.SNAPSHOT)
                        .readPreference(ReadPreference.primary())
                        .build());
    }

    public MongoTemplate operate() {
        return mongoTemplate;
    }

    public void commitTransaction() {
        session.commitTransaction();
    }

    public boolean isTransactionActive() {
        return session.hasActiveTransaction();
    }

    public void abortTransaction() {
        session.abortTransaction();
    }

    public void closeSession() {
        session.close();
    }
}
