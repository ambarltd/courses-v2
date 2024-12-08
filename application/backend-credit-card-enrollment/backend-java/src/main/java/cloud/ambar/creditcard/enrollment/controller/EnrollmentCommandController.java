package cloud.ambar.creditcard.enrollment.controller;

import cloud.ambar.common.commandhandler.CommandController;
import cloud.ambar.common.eventstore.EventStore;
import cloud.ambar.common.sessionauth.SessionService;
import cloud.ambar.creditcard.enrollment.commandhandler.RequestEnrollmentCommandHandler;
import cloud.ambar.creditcard.enrollment.commandhandler.RequestEnrollmentCommand;
import jakarta.validation.Valid;
import org.springframework.http.HttpStatus;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.context.annotation.RequestScope;

@RestController
@RequestScope
@RequestMapping("/api/v1/credit_card/enrollment")
public class EnrollmentCommandController extends CommandController {
    private final SessionService sessionService;

    private final RequestEnrollmentCommandHandler requestEnrollmentCommandHandler;

    public EnrollmentCommandController(
            EventStore eventStore,
            SessionService sessionService,
            RequestEnrollmentCommandHandler requestEnrollmentCommandHandler
    ) {
        super(eventStore);
        this.sessionService = sessionService;
        this.requestEnrollmentCommandHandler = requestEnrollmentCommandHandler;
    }

    @PostMapping("/request-enrollment")
    @ResponseStatus(HttpStatus.OK)
    public void requestEnrollment(
            @RequestHeader("X-With-Session-Token") String sessionToken,
            @Valid @RequestBody RequestEnrollmentHttpRequest request) {
        final RequestEnrollmentCommand command = RequestEnrollmentCommand
                .builder()
                .userId(sessionService.authenticatedUserIdFromSessionToken(sessionToken))
                .productId(request.getProductId())
                .annualIncomeInCents(request.getAnnualIncomeInCents())
                .build();

        processCommand(command, requestEnrollmentCommandHandler);
    }
}
