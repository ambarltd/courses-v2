using CreditCardEnrollment.Common.Ambar;
using CreditCardEnrollment.Common.EventStore;
using CreditCardEnrollment.Common.Projection;
using CreditCardEnrollment.Common.SerializedEvent;

namespace CreditCardEnrollment.Common.Reaction;

public abstract class ReactionController {
    private readonly PostgresTransactionalEventStore _postgresTransactionalEventStore;
    private readonly MongoTransactionalProjectionOperator _mongoTransactionalProjectionOperator;
    private readonly Deserializer _deserializer;
    private readonly ILogger<ReactionController> _logger;

    protected ReactionController(
        PostgresTransactionalEventStore postgresTransactionalEventStore,
        MongoTransactionalProjectionOperator mongoTransactionalProjectionOperator,
        Deserializer deserializer,
        ILogger<ReactionController> logger
    ) {
        _postgresTransactionalEventStore = postgresTransactionalEventStore;
        _mongoTransactionalProjectionOperator = mongoTransactionalProjectionOperator;
        _deserializer = deserializer;
        _logger = logger;
    }

    protected string ProcessReactionHttpRequest(AmbarHttpRequest ambarHttpRequest, ReactionHandler reactionHandler) {
        try {
            _postgresTransactionalEventStore.BeginTransaction();
            _mongoTransactionalProjectionOperator.StartTransaction();
            reactionHandler.React(_deserializer.Deserialize(ambarHttpRequest.SerializedEvent));
            _postgresTransactionalEventStore.CommitTransaction();
            _mongoTransactionalProjectionOperator.CommitTransaction();

            _postgresTransactionalEventStore.AbortDanglingTransactionsAndReturnConnectionToPool();
            _mongoTransactionalProjectionOperator.AbortDanglingTransactionsAndReturnSessionToPool();

            return AmbarResponseFactory.SuccessResponse();
        } catch (Exception ex) when (ex.Message?.StartsWith("Unknown event type") == true) {
            _postgresTransactionalEventStore.AbortDanglingTransactionsAndReturnConnectionToPool();
            _mongoTransactionalProjectionOperator.AbortDanglingTransactionsAndReturnSessionToPool();
            return AmbarResponseFactory.SuccessResponse();
        } catch (Exception ex) {
            _postgresTransactionalEventStore.AbortDanglingTransactionsAndReturnConnectionToPool();
            _mongoTransactionalProjectionOperator.AbortDanglingTransactionsAndReturnSessionToPool();
            _logger.LogError("Exception in ProcessReactionHttpRequest: {0}, {1}", ex.Message, ex.StackTrace);
            return AmbarResponseFactory.RetryResponse(ex);
        }
    }
}