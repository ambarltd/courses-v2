import "reflect-metadata";
import { container, Lifecycle } from "tsyringe";
import { PostgresConnectionPool } from "../common/util/PostgresConnectionPool";
import { MongoSessionPool } from "../common/util/MongoSessionPool";
import { Deserializer } from "../common/serializedEvent/Deserializer";
import { Serializer } from "../common/serializedEvent/Serializer";
import { PostgresTransactionalEventStore } from "../common/eventStore/PostgresTransactionalEventStore";
import { MongoTransactionalProjectionOperator } from "../common/projection/MongoTransactionalProjectionOperator";
import { SessionRepository } from "../common/sessionAuth/SessionRepository";
import { SessionService } from "../common/sessionAuth/SessionService";
import { ProductActiveStatusRepository } from "../creditCard/enrollment/projection/isProductActive/ProductActiveStatusRepository";
import { IsProductActive } from "../creditCard/enrollment/projection/isProductActive/IsProductActive";
import { EnrollmentRepository } from "../creditCard/enrollment/projection/enrollmentList/EnrollmentRepository";
import { ProductNameRepository } from "../creditCard/enrollment/projection/enrollmentList/ProductNameRepository";
import { GetEnrollmentList } from "../creditCard/enrollment/projection/enrollmentList/GetEnrollmentList";
import { IsProductActiveProjectionHandler } from "../creditCard/enrollment/projection/isProductActive/IsProductActiveProjectionHandler";
import { EnrollmentListProjectionHandler } from "../creditCard/enrollment/projection/enrollmentList/EnrollmentListProjectionHandler";
import { RequestEnrollmentCommandHandler } from "../creditCard/enrollment/command/RequestEnrollmentCommandHandler";
import { GetUserEnrollmentsQueryHandler } from "../creditCard/enrollment/query/GetUserEnrollmentsQueryHandler";
import { ReviewEnrollmentReactionHandler } from "../creditCard/enrollment/reaction/ReviewEnrollmentReactionHandler";
import { constructor } from "tsyringe/dist/typings/types";
import {EnrollmentProjectionController} from "../creditCard/enrollment/projection/EnrollmentProjectionController";
import {EnrollmentQueryController} from "../creditCard/enrollment/query/EnrollmentQueryController";
import {EnrollmentCommandController} from "../creditCard/enrollment/command/EnrollmentCommandController";
import {EnrollmentReactionController} from "../creditCard/enrollment/reaction/EnrollmentReactionController";
import {MongoInitializer} from "../common/util/MongoInitializer";
import {PostgresInitializer} from "../common/util/PostgresInitializer";

function registerEnvironmentVariables() {
    const postgresConnectionString =
        `postgresql://${getEnvVar("EVENT_STORE_USER")}:${getEnvVar("EVENT_STORE_PASSWORD")}@` +
        `${getEnvVar("EVENT_STORE_HOST")}:${getEnvVar("EVENT_STORE_PORT")}/` +
        `${getEnvVar("EVENT_STORE_DATABASE_NAME")}`;
    container.register("postgresConnectionString", { useValue: postgresConnectionString });
    container.register("eventStoreTable", { useValue: getEnvVar('EVENT_STORE_CREATE_TABLE_WITH_NAME')});
    container.register("eventStoreDatabaseName", {useValue: getEnvVar('EVENT_STORE_DATABASE_NAME')});
    container.register("eventStoreCreateReplicationUserWithUsername", { useValue: getEnvVar('EVENT_STORE_CREATE_REPLICATION_USER_WITH_USERNAME')});
    container.register("eventStoreCreateReplicationUserWithPassword", { useValue: getEnvVar('EVENT_STORE_CREATE_REPLICATION_USER_WITH_PASSWORD')});
    container.register("eventStoreCreateReplicationPublication", {useValue: getEnvVar('EVENT_STORE_CREATE_REPLICATION_PUBLICATION')});

    const mongoConnectionString =
        `mongodb://${getEnvVar("MONGODB_PROJECTION_DATABASE_USERNAME")}:${getEnvVar("MONGODB_PROJECTION_DATABASE_PASSWORD")}@` +
        `${getEnvVar("MONGODB_PROJECTION_HOST")}:${getEnvVar("MONGODB_PROJECTION_PORT")}/` +
        `${getEnvVar("MONGODB_PROJECTION_DATABASE_NAME")}` +
        "?serverSelectionTimeoutMS=10000&connectTimeoutMS=10000&authSource=admin";
    const mongoDatabaseName = getEnvVar("MONGODB_PROJECTION_DATABASE_NAME");
    container.register("mongoConnectionString", { useValue: mongoConnectionString });
    container.register("mongoDatabaseName", { useValue: mongoDatabaseName });

    const sessionExpirationSeconds = parseInt(getEnvVar("SESSION_TOKENS_EXPIRE_AFTER_SECONDS"));
    container.register("sessionExpirationSeconds", { useValue: sessionExpirationSeconds });
}

function registerSingletons() {
    // common/serializedEvent
    container.registerSingleton(Serializer);
    container.registerSingleton(Deserializer);

    // common/util
    container.registerSingleton(PostgresConnectionPool);
    container.registerSingleton(MongoSessionPool);
    container.registerSingleton(MongoInitializer);
    container.registerSingleton(PostgresInitializer);
}

function registerScoped<T>(token: constructor<T>) {
    container.register(token, token, { lifecycle: Lifecycle.ContainerScoped });
}

function registerScopedServices() {
    // common/eventStore
    registerScoped(PostgresTransactionalEventStore);

    // common/projection
    registerScoped(MongoTransactionalProjectionOperator);

    // common/session
    registerScoped(SessionRepository);
    registerScoped(SessionService);

    // creditCard/enrollment/command
    registerScoped(RequestEnrollmentCommandHandler);
    registerScoped(EnrollmentCommandController);

    // creditCard/enrollment/projection
    registerScoped(EnrollmentProjectionController);

    registerScoped(EnrollmentListProjectionHandler);
    registerScoped(EnrollmentRepository);
    registerScoped(GetEnrollmentList);
    registerScoped(ProductNameRepository);

    registerScoped(IsProductActive);
    registerScoped(IsProductActiveProjectionHandler);
    registerScoped(ProductActiveStatusRepository);

    // creditCard/enrollment/query
    registerScoped(GetUserEnrollmentsQueryHandler);
    registerScoped(EnrollmentQueryController);

    // creditCard/enrollment/reaction
    registerScoped(ReviewEnrollmentReactionHandler);
    registerScoped(EnrollmentReactionController);
}

export function configureDependencies() {
    registerEnvironmentVariables();
    registerSingletons();
    registerScopedServices();
}

function getEnvVar(name: string): string {
    const value = process.env[name];
    if (!value) {
        throw new Error(`Environment variable ${name} is not defined`);
    }
    return value;
}