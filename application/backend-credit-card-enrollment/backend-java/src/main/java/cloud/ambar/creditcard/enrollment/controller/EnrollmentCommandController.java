package cloud.ambar.creditcard.enrollment.controller;

import cloud.ambar.common.sessionauth.SessionService;
import cloud.ambar.creditcard.enrollment.commandhandler.EnrollmentCommandHandler;
import cloud.ambar.creditcard.enrollment.commandhandler.RequestEnrollmentCommand;
import jakarta.validation.Valid;
import lombok.RequiredArgsConstructor;
import org.springframework.http.HttpStatus;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.context.annotation.RequestScope;

@RestController
@RequestScope
@RequiredArgsConstructor
@RequestMapping("/api/v1/credit_card/enrollment")
public class EnrollmentCommandController {
    private final SessionService sessionService;

    private final EnrollmentCommandHandler enrollmentService;

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

        enrollmentService.handle(command);
    }
}
