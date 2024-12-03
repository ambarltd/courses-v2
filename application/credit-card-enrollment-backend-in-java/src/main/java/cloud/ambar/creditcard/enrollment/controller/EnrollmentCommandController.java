package cloud.ambar.creditcard.enrollment.controller;

import cloud.ambar.common.sessionauth.SessionService;
import cloud.ambar.creditcard.enrollment.command.EnrollmentCommandHandler;
import cloud.ambar.creditcard.enrollment.command.RequestEnrollmentCommand;
import cloud.ambar.creditcard.enrollment.command.SubmitEnrollmentForReviewCommand;
import jakarta.validation.Valid;
import lombok.RequiredArgsConstructor;
import org.springframework.http.HttpStatus;
import org.springframework.web.bind.annotation.*;

@RestController
@RequiredArgsConstructor
@RequestMapping("/api/v1/credit_card/enrollment")
public class EnrollmentCommandController {
    private final SessionService sessionService;

    private final EnrollmentCommandHandler enrollmentService;

    @PostMapping("/request")
    @ResponseStatus(HttpStatus.OK)
    public void requestEnrollment(
            @RequestHeader("X-With-Session-Token") String sessionToken,
            @Valid @RequestBody RequestEnrollmentHttpRequest request) {
        final RequestEnrollmentCommand command = RequestEnrollmentCommand
                .builder()
                .userId(sessionService.authenticatedUserIdFromSessionToken(sessionToken))
                .productId(request.getProductId())
                .annualIncome(request.getAnnualIncome())
                .build();

        enrollmentService.handle(command);
    }


    @PostMapping("/submit_for_review")
    @ResponseStatus(HttpStatus.OK)
    public void submitForReview(
            @RequestHeader("X-With-Session-Token") String sessionToken,
            @Valid @RequestBody SubmitEnrollmentForReviewHttpRequest request) {
        final SubmitEnrollmentForReviewCommand command = SubmitEnrollmentForReviewCommand
                .builder()
                .enrollmentId(request.getEnrollmentId())
                .userId(sessionService.authenticatedUserIdFromSessionToken(sessionToken))
                .build();

        enrollmentService.handle(command);
    }}
