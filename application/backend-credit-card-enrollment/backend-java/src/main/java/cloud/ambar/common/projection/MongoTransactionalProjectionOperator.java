package cloud.ambar.common.projection;

import com.mongodb.ReadConcern;
import com.mongodb.ReadPreference;
import com.mongodb.TransactionOptions;
import com.mongodb.WriteConcern;
import com.mongodb.client.ClientSession;
import lombok.RequiredArgsConstructor;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.springframework.data.mongodb.core.MongoTemplate;

import java.sql.SQLException;

@RequiredArgsConstructor
public class MongoTransactionalProjectionOperator {
    private static final Logger log = LogManager.getLogger(MongoTransactionalProjectionOperator.class);

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

    public void abortDanglingTransactionsAndReturnSessionToPool() {
        log.info("MongoTransactionalProjectionOperator: Aborting dangling transactions and returning connection to pool.");
        if (session.hasActiveTransaction()) {
            session.abortTransaction();
        }
        session.close();
    }
}
