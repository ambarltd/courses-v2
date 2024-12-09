package cloud.ambar.common.projection;

import com.mongodb.ReadConcern;
import com.mongodb.ReadPreference;
import com.mongodb.TransactionOptions;
import com.mongodb.WriteConcern;
import com.mongodb.client.ClientSession;
import lombok.RequiredArgsConstructor;
import org.springframework.data.mongodb.core.MongoTemplate;

@RequiredArgsConstructor
public class MongoTransactionalProjectionOperator implements AutoCloseable {
    private final MongoTemplate mongoTemplate;

    private final ClientSession session;

    public void startTransaction() {
        if (session.hasActiveTransaction()) {
            throw new RuntimeException("Transaction to MongoDB already active!");
        }
        session.startTransaction(
                TransactionOptions.builder()
                        .writeConcern(WriteConcern.MAJORITY)
                        .readConcern(ReadConcern.SNAPSHOT)
                        .readPreference(ReadPreference.primary())
                        .build());
    }

    // This method is used to read and write to MongoDB within a transaction.
    public MongoTemplate operate() {
        if (!session.hasActiveTransaction()) {
            throw new RuntimeException("Transaction must be active to read or write to MongoDB!");
        }
        return mongoTemplate;
    }

    public void commitTransaction() {
        if (!session.hasActiveTransaction()) {
            throw new RuntimeException("Transaction must be active to commit transaction to MongoDB!");
        }
        session.commitTransaction();
    }

    public boolean isTransactionActive() {
        return session.hasActiveTransaction();
    }

    public void abortTransaction() {
        if (!session.hasActiveTransaction()) {
            throw new RuntimeException("Transaction must be active to abort transaction for MongoDB!");
        }
        session.abortTransaction();
    }

    // IMPLEMENTATION OF AutoCloseable INTERFACE - cleanly close dangling transactions
    // when the transactional projection operator gets garbage collected.
    // I.e., it will return the projection operator's session back to the connection pool.
    // Note: There is no need to close the session, because that would mess with the library's session pool.
    // The transactional projection operator is meant to be used in @RequestScope, so the session will be cleaned up
    // by the library when the transactional projection operator and its session are garbage collected.
    public void close() {
        if (session.hasActiveTransaction()) {
            session.abortTransaction();
        }
    }
}
