import React, { useEffect, useRef, useState } from 'react';
import { Link, RouteComponentProps } from 'react-router-dom';
import login from '@/api/auth/login';
import LoginFormContainer from '@/components/auth/LoginFormContainer';
import { useStoreState } from 'easy-peasy';
import { Formik, FormikHelpers } from 'formik';
import { object, string } from 'yup';
import Field from '@/components/elements/Field';
import tw from 'twin.macro';
import Button from '@/components/elements/Button';
import Reaptcha from 'reaptcha';
import useFlash from '@/plugins/useFlash';

interface Values {
    username: string;
    password: string;
}

const LoginContainer = ({ history }: RouteComponentProps) => {
    const ref = useRef<Reaptcha>(null);
    const pendingCaptchaExecution = useRef(false);
    const tokenRef = useRef('');
    const [token, setToken] = useState('');
    const [captchaReady, setCaptchaReady] = useState(false);

    const { clearFlashes, clearAndAddHttpError } = useFlash();
    const { enabled: recaptchaEnabled, siteKey } = useStoreState((state) => state.settings.data!.recaptcha);
    const canUseRecaptcha = Boolean(recaptchaEnabled && siteKey && siteKey !== '_invalid_key');

    useEffect(() => {
        clearFlashes();
    }, []);

    useEffect(() => {
        tokenRef.current = token;
    }, [token]);

    const onSubmit = (values: Values, { setSubmitting }: FormikHelpers<Values>) => {
        clearFlashes();

        // If there is no token in the state yet, request the token and then abort this submit request
        // since it will be re-submitted when the recaptcha data is returned by the component.
        if (canUseRecaptcha && !tokenRef.current) {
            if (!captchaReady || !ref.current) {
                pendingCaptchaExecution.current = true;
                setSubmitting(false);
                return;
            }

            pendingCaptchaExecution.current = false;
            setSubmitting(false);
            ref.current.execute().catch((error) => {
                console.error(error);

                setSubmitting(false);
                clearAndAddHttpError({ error });
            });

            return;
        }

        login({ ...values, recaptchaData: tokenRef.current })
            .then((response) => {
                if (response.complete) {
                    // @ts-expect-error this is valid
                    window.location = response.intended || '/';
                    return;
                }

                history.replace('/auth/login/checkpoint', { token: response.confirmationToken });
            })
            .catch((error) => {
                console.error(error);

                tokenRef.current = '';
                setToken('');
                if (ref.current) ref.current.reset();

                setSubmitting(false);
                clearAndAddHttpError({ error });
            });
    };

    return (
        <Formik
            onSubmit={onSubmit}
            initialValues={{ username: '', password: '' }}
            validationSchema={object().shape({
                username: string().required('A username or email must be provided.'),
                password: string().required('Please enter your account password.'),
            })}
        >
            {({ isSubmitting, setSubmitting, submitForm }) => (
                <LoginFormContainer title={'Welcome back'} css={tw`w-full flex`}>
                    <Field type={'text'} label={'Username or Email'} name={'username'} disabled={isSubmitting} />
                    <div css={tw`mt-6`}>
                        <Field type={'password'} label={'Password'} name={'password'} disabled={isSubmitting} />
                    </div>
                    <div css={tw`mt-6`}>
                        <Button type={'submit'} size={'xlarge'} isLoading={isSubmitting} disabled={isSubmitting}>
                            Sign in
                        </Button>
                    </div>
                    {canUseRecaptcha && (
                        <Reaptcha
                            ref={ref}
                            size={'invisible'}
                            sitekey={siteKey || '_invalid_key'}
                            onLoad={() => {
                                setCaptchaReady(true);
                                if (pendingCaptchaExecution.current && ref.current) {
                                    pendingCaptchaExecution.current = false;
                                    ref.current.execute().catch((error) => {
                                        console.error(error);
                                    });
                                }
                            }}
                            onVerify={(response) => {
                                tokenRef.current = response;
                                setToken(response);
                                submitForm();
                            }}
                            onExpire={() => {
                                setSubmitting(false);
                                tokenRef.current = '';
                                setToken('');
                            }}
                        />
                    )}
                    <div css={tw`mt-6 text-center`}>
                        <Link
                            to={'/auth/password'}
                            css={tw`text-xs text-neutral-500 tracking-wide no-underline uppercase hover:text-neutral-600`}
                        >
                            Forgot password?
                        </Link>
                    </div>
                </LoginFormContainer>
            )}
        </Formik>
    );
};

export default LoginContainer;
