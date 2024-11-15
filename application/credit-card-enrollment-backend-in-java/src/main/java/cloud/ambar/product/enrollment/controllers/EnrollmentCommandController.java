package cloud.ambar.product.enrollment.controllers;

import cloud.ambar.common.users.models.UserSession;
import cloud.ambar.common.users.service.UserSessionService;
import cloud.ambar.product.enrollment.commands.ProductEnrollmentCommandService;
import cloud.ambar.product.enrollment.commands.models.RequestEnrollmentCommand;
import cloud.ambar.product.enrollment.controllers.requests.EnrollmentRequest;
import cloud.ambar.product.enrollment.exceptions.InvalidIncomeException;
import cloud.ambar.product.enrollment.exceptions.InvalidProductException;
import cloud.ambar.product.enrollment.exceptions.InvalidUserException;
import com.fasterxml.jackson.core.JsonProcessingException;
import lombok.RequiredArgsConstructor;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.springframework.http.HttpStatus;
import org.springframework.stereotype.Controller;
import org.springframework.util.ObjectUtils;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestHeader;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.ResponseStatus;

import java.util.NoSuchElementException;


@Controller
@RequiredArgsConstructor
@RequestMapping("/api/v1/credit_card_product/enrollment")
public class EnrollmentCommandController {
    private static final Logger log = LogManager.getLogger(EnrollmentCommandController.class);

    private final UserSessionService userSessionService;

    private final ProductEnrollmentCommandService enrollmentService;


    @PostMapping
    @ResponseStatus(HttpStatus.OK)
    public void requestEnrollment(
            @RequestHeader("X-With-Session-Token") String sessionToken,
            @RequestBody EnrollmentRequest request) throws JsonProcessingException {
        log.info("Request for enrollment.");

        // Get the session token and make sure there is a user associated with it.
        final UserSession session = getSessionForToken(sessionToken);

        // Light request validations.
        // Further validations on aggregates in the service.
        validateProductNotNull(request.getProductId());
        validateAnnualIncomeNotZero(request.getAnnualIncome());

        final RequestEnrollmentCommand command = new RequestEnrollmentCommand();
        command.setAnnualIncome(request.getAnnualIncome());
        command.setProductId(request.getProductId());
        command.setUserId(session.getUserId());

        enrollmentService.handle(command);
    }

    private UserSession getSessionForToken(String sessionToken) {
        log.info("Validating session token present and valid.");
        try {
            return userSessionService.getUserSessionForToken(sessionToken);
        } catch (NoSuchElementException e) {
            log.info("Unable to find valid session for token: " + sessionToken);
            throw new InvalidUserException();
        }
    }

    private void validateAnnualIncomeNotZero(int annualIncome) {
        log.info("Validating passed annual income is reasonable value.");
        if (annualIncome > 0) {
            return;
        }
        log.info("Given income '" + annualIncome + "' not a positive value.");
        throw new InvalidIncomeException();
    }
    private void validateProductNotNull(String productId) {
        log.info("Validating product exists and is valid.");
        if (!ObjectUtils.isEmpty(productId)) {
            return;
        }
        log.info("ProductId '" + productId + "' not found or is empty.");
        throw new InvalidProductException();
    }
}
