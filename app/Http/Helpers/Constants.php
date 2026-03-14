<?php

namespace App\Http\Helpers;

class Constants {
    
    //tablas
    const TABLE_ATTENDEES = 'attendees';
    const TABLE_BANK_DETAILS = 'bank_details';
    const TABLE_BANNERS = 'banners';
    const TABLE_CANDIDATES = 'candidates';
    const TABLE_CLINICS = 'clinics';
    const TABLE_CONFERENCES = 'conferences';
    const TABLE_COURSES = 'courses';
    const TABLE_DIRECTORY_DATA = 'directory_data';
    const TABLE_INVOICE_DATA = 'invoice_data';
    const TABLE_MEDIA = 'media';
    const TABLE_MEMBERS = 'members';
    const TABLE_MEMBERSHIPS = 'memberships';
    const TABLE_MEMBERSHIP_PRICES = 'membership_prices';
    const TABLE_NEWS = 'news';
    const TABLE_PAYMENT_METHODS = 'payment_methods';
    const TABLE_PAYMENTS = 'payments';
    const TABLE_POSTS = 'posts';
    const TABLE_USERS = 'users';
    const TABLE_WEBINARS = 'webinars';
    const TABLE_EVENT_SESSIONS = 'event_sessions';
    const TABLE_ACADEMIC_SESSIONS = 'academic_sessions';
    const TABLE_ALBUMS = 'albums';


    //tipos de evento
    const EVENT_CONFERENCE = 'conference'; //congreso
    const EVENT_COURSE = 'course';
    const EVENT_WEBINAR = 'webinar';
    const EVENT_PRE_CONFERENCE = 'pre_conference'; //pre-congreso
    const EVENT_TRANS_CONFERENCE = 'trans_conference'; //pre-congreso
    const EVENT_ACADEMIC_SESSION = 'academic_session'; //sesion academica

    //tipos de aistentes
    const PERSON_MEMBER = 'member';
    const PERSON_GUEST = 'guest';
    const PERSON_RESIDENT = 'resident';
    const PERSON_NURSE = 'nurse';
    const PERSON_SURGEON = 'surgeon';

    //metodos de pago
    const METHOD_CASH = 'cash'; //efectivo
    const METHOD_DEBIT_CARD = 'debit_card';
    const METHOD_CREDIT_CARD = 'credit_card';
    const METHOD_STRIPE = 'stripe';
    const METHOD_TRANSFER = 'transfer'; //trasferencia
    const METHOD_FREE = 'free'; //gratuito

    //status de pago
    const STATUS_PAID = 'paid';
    const STATUS_PENDING = 'pending';
    const STATUS_CANCELLED = 'cancelled';

    


}
